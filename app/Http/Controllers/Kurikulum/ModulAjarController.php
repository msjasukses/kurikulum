<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Concerns\FilterPenugasanGuru;
use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Models\ModulAjar;
use App\Models\PemetaanCpTpAtp;
use App\Models\TingkatKelas;
use App\Services\GeneratorModulAjarAi;
use App\Support\TahunAjaranTerpilih;
use App\Support\TeksKaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Generator Modul Ajar Digital: form penyusunan modul (identitas pertemuan,
 * pilihan CP/TP/ATP dari Pemetaan, isi modul per bagian, LKPD) plus daftar
 * modul dengan aksi unduh PDF/Word/LKPD.
 */
class ModulAjarController extends Controller
{
    use FilterPenugasanGuru;

    public function index(Request $request): View
    {
        $tahunAjaran = app(TahunAjaranTerpilih::class);
        $tahunList = $tahunAjaran->daftar();
        $namaTahun = $tahunAjaran->nama();

        // Pemetaan CP-TP-ATP dikirim ke halaman sebagai JSON untuk dropdown
        // bertingkat CP -> TP -> ATP (difilter di sisi klien per mapel+tingkat).
        // Data lama yang belum berlabel tahun ajaran tetap ikut ditampilkan.
        $pemetaanList = PemetaanCpTpAtp::query()
            ->when($namaTahun, fn ($q) => $q->where(
                fn ($x) => $x->where('tahun_ajaran', $namaTahun)->orWhereNull('tahun_ajaran')
            ))
            ->when($this->mapelIdsGuru() !== null, fn ($q) => $q->whereIn('mata_pelajaran_id', $this->mapelIdsGuru()))
            ->get([
                'id', 'mata_pelajaran_id', 'tingkat_kelas_id',
                'capaian_pembelajaran', 'tujuan_pembelajaran', 'alur_tujuan_pembelajaran',
            ])
            // CP/TP/ATP kini boleh diketik lewat editor teks kaya, jadi untuk
            // isi dropdown dan hasil generate dipakai versi teks polosnya.
            ->map(fn ($p) => [
                'id' => $p->id,
                'mata_pelajaran_id' => $p->mata_pelajaran_id,
                'tingkat_kelas_id' => $p->tingkat_kelas_id,
                'capaian_pembelajaran' => TeksKaya::polos($p->capaian_pembelajaran),
                'tujuan_pembelajaran' => TeksKaya::polos($p->tujuan_pembelajaran),
                'alur_tujuan_pembelajaran' => TeksKaya::polos($p->alur_tujuan_pembelajaran),
            ]);

        // Untuk guru, pilihan mapel & tingkat dibatasi sesuai penugasan di
        // menu Data Guru Mata Pelajaran (datacenter); admin melihat semua.
        $mapelIds = $this->mapelIdsGuru();
        $tingkatIds = $this->tingkatIdsGuru();

        return view('kurikulum.modul-ajar', [
            'mapelList' => MataPelajaran::when($mapelIds !== null, fn ($q) => $q->whereIn('id', $mapelIds))
                ->orderBy('nama_mapel')->get(),
            'tingkatList' => TingkatKelas::when($tingkatIds !== null, fn ($q) => $q->whereIn('id', $tingkatIds))
                ->orderBy('urutan')->get(),
            'tahunList' => $tahunList,
            'tahunAktif' => $tahunAjaran->terpilih(),
            'pemetaanJson' => $pemetaanList->toJson(JSON_UNESCAPED_UNICODE),
            'items' => ModulAjar::with(['mataPelajaran', 'tingkatKelas', 'pegawai'])
                ->when($namaTahun, fn ($q) => $q->where(
                    fn ($x) => $x->where('tahun_ajaran', $namaTahun)->orWhereNull('tahun_ajaran')
                ))
                ->when($mapelIds !== null, fn ($q) => $q->whereIn('mata_pelajaran_id', $mapelIds))
                ->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        // CP/TP/ATP disalin dari baris pemetaan terpilih supaya isi modul tetap
        // utuh walau data pemetaan diubah/dihapus di kemudian hari.
        if (! empty($data['pemetaan_cp_tp_atp_id'])) {
            $pemetaan = PemetaanCpTpAtp::find($data['pemetaan_cp_tp_atp_id']);
            if ($pemetaan) {
                $data['capaian_pembelajaran'] = $pemetaan->capaian_pembelajaran;
                $data['tujuan_pembelajaran'] = $pemetaan->tujuan_pembelajaran;
                $data['alur_tujuan_pembelajaran'] = $pemetaan->alur_tujuan_pembelajaran;
                $data['fase'] = $pemetaan->fase;
            }
        }

        $data['pegawai_id'] = auth()->user()->pegawai_id;

        $modul = ModulAjar::create($data);

        if ($request->boolean('download_pdf')) {
            return redirect()->route('kurikulum.modul-ajar.pdf', $modul->id);
        }

        return redirect()->route('kurikulum.modul-ajar.index')
            ->with('success', 'Modul ajar berhasil disimpan.');
    }

    /**
     * Susun isi modul & LKPD dengan AI dari konteks yang sudah dipilih di
     * form. Penyedia dan kunci API mengikuti pengaturan akun yang login
     * (Setting Profil). Dipanggil lewat AJAX oleh tombol "Generate Modul Ajar".
     */
    public function generate(Request $request, GeneratorModulAjarAi $ai): JsonResponse
    {
        $user = $request->user();

        if (! $ai->tersedia($user)) {
            return response()->json([
                'tersedia' => false,
                'pesan' => 'Kunci API AI belum diatur. Isi dulu di menu Setting Profil — untuk sementara modul disusun memakai kerangka bawaan aplikasi.',
            ], 200);
        }

        $data = $request->validate([
            'mata_pelajaran_id' => 'required|integer',
            'tingkat_kelas_id' => 'required|integer',
            'judul' => 'required|string|max:150',
            'jumlah_jam' => 'nullable|integer|min:1',
            'pertemuan_ke' => 'nullable|integer|min:1',
            'semester' => 'nullable|string|max:20',
            'pemetaan_cp_tp_atp_id' => 'nullable|integer|exists:pemetaan_cp_tp_atp,id',
            'capaian_pembelajaran' => 'nullable|string',
            'tujuan_pembelajaran' => 'nullable|string',
        ], [], ['judul' => 'Judul Materi']);

        $konteks = [
            'mapel' => optional(MataPelajaran::find($data['mata_pelajaran_id']))->nama_mapel,
            'tingkat' => optional(TingkatKelas::find($data['tingkat_kelas_id']))->nama,
            'semester' => $data['semester'] ?? null,
            'jumlah_jam' => $data['jumlah_jam'] ?? null,
            'pertemuan_ke' => $data['pertemuan_ke'] ?? null,
            'judul' => $data['judul'],
            'cp' => TeksKaya::polos($data['capaian_pembelajaran'] ?? null) ?: null,
            'tp' => TeksKaya::polos($data['tujuan_pembelajaran'] ?? null) ?: null,
        ];

        // Bila ATP dipilih, CP/TP/ATP diambil dari baris pemetaannya supaya
        // isinya utuh (bukan teks terpotong dari dropdown).
        if (! empty($data['pemetaan_cp_tp_atp_id']) && $pemetaan = PemetaanCpTpAtp::find($data['pemetaan_cp_tp_atp_id'])) {
            $konteks['cp'] = TeksKaya::polos($pemetaan->capaian_pembelajaran) ?: $konteks['cp'];
            $konteks['tp'] = TeksKaya::polos($pemetaan->tujuan_pembelajaran) ?: $konteks['tp'];
            $konteks['atp'] = TeksKaya::polos($pemetaan->alur_tujuan_pembelajaran) ?: null;
            $konteks['fase'] = $pemetaan->fase;
        }

        // Penyusunan bisa memakan waktu puluhan detik, jangan dipotong server.
        set_time_limit(180);

        try {
            $isi = $ai->generate($konteks, $user);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'tersedia' => true,
                'pesan' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['tersedia' => true, 'isi' => $isi]);
    }

    public function destroy(int $id): RedirectResponse
    {
        ModulAjar::findOrFail($id)->delete();

        return redirect()->route('kurikulum.modul-ajar.index')
            ->with('success', 'Modul ajar berhasil dihapus.');
    }

    /** Unduh modul sebagai PDF (dompdf) atau tampilan cetak bila dompdf tak tersedia. */
    public function pdf(int $id)
    {
        return $this->render($id, 'modul', 'pdf');
    }

    /** Unduh modul sebagai dokumen Word (.doc berbasis HTML). */
    public function word(int $id)
    {
        return $this->render($id, 'modul', 'word');
    }

    /** Unduh LKPD (bagian lembar kerja peserta didik saja) sebagai PDF. */
    public function lkpd(int $id)
    {
        return $this->render($id, 'lkpd', 'pdf');
    }

    private function render(int $id, string $bagian, string $format)
    {
        $modul = ModulAjar::with(['mataPelajaran', 'tingkatKelas', 'pegawai'])->findOrFail($id);
        $namaFile = ($bagian === 'lkpd' ? 'lkpd-' : 'modul-ajar-').\Illuminate\Support\Str::slug($modul->judul ?: $modul->id);

        $view = view('kurikulum.modul-ajar-cetak', [
            'modul' => $modul,
            'bagian' => $bagian,
            'autoPrint' => false,
        ]);

        if ($format === 'word') {
            return response($view->render(), 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$namaFile.'.doc"',
            ]);
        }

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($view->render())
                ->setPaper('a4')
                ->download($namaFile.'.pdf');
        }

        // Fallback tanpa dompdf: tampilan cetak yang langsung membuka dialog
        // print browser (bisa disimpan sebagai PDF dari sana).
        return view('kurikulum.modul-ajar-cetak', [
            'modul' => $modul,
            'bagian' => $bagian,
            'autoPrint' => true,
        ]);
    }

    private function validated(Request $request): array
    {
        $rules = [
            'mata_pelajaran_id' => 'required|integer',
            'tingkat_kelas_id' => 'required|integer',
            'jumlah_jam' => 'required|integer|min:1',
            'pertemuan_ke' => 'required|integer|min:1',
            'semester' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:20',
            'pemetaan_cp_tp_atp_id' => 'nullable|integer|exists:pemetaan_cp_tp_atp,id',
            'judul' => 'required|string|max:150',
        ];

        foreach (array_keys(ModulAjar::BAGIAN_ISI + ModulAjar::BAGIAN_LKPD) as $name) {
            $rules[$name] = 'nullable|string';
        }

        $data = $request->validate($rules, [], [
            'mata_pelajaran_id' => 'Mata Pelajaran',
            'tingkat_kelas_id' => 'Tingkat',
            'jumlah_jam' => 'Jumlah Jam',
            'pertemuan_ke' => 'Pertemuan Ke',
            'tahun_ajaran' => 'Tahun Ajaran',
            'pemetaan_cp_tp_atp_id' => 'Pilihan ATP',
            'judul' => 'Judul Materi',
        ]);

        // Isi bagian modul & LKPD datang dari editor TinyMCE, jadi HTML-nya
        // disaring dulu sebelum disimpan.
        foreach (array_keys(ModulAjar::BAGIAN_ISI + ModulAjar::BAGIAN_LKPD) as $name) {
            if (array_key_exists($name, $data)) {
                $data[$name] = ModulAjar::bersihkanHtml($data[$name]);
            }
        }

        return $data;
    }
}
