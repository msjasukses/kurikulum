<?php

namespace App\Http\Controllers\Pkl;

use App\Http\Controllers\Controller;
use App\Models\IdentitasSekolah;
use App\Models\JadwalMagang;
use App\Models\JurnalPkl;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

/**
 * Menu Jurnal PKL / Magang.
 *
 * Siswa mengisi jurnal harian hanya pada tanggal yang terjadwal magang di
 * aplikasi Absensi (tabel jadwal_magang: periode + hari magang). Admin dan
 * guru memantau keterisian jurnal, memeriksa (setujui / minta revisi), dan
 * mencetak jurnal per siswa.
 */
class JurnalPklController extends Controller
{
    public function index(Request $request): View
    {
        try {
            return $request->user()->isSiswa()
                ? $this->indexSiswa($request)
                : $this->indexPembimbing($request);
        } catch (Throwable $e) {
            report($e);

            // Database Absensi tidak terjangkau / belum dikonfigurasi.
            return view('pkl.jurnal.koneksi-gagal');
        }
    }

    /** Halaman siswa: jadwal magangnya + daftar hari magang beserta status jurnal. */
    private function indexSiswa(Request $request): View
    {
        $siswa = $this->siswaLogin($request);
        $jadwalList = JadwalMagang::milik($siswa)->orderByDesc('tanggal_mulai')->get();

        $jurnal = JurnalPkl::where('siswa_id', $siswa->id)->get()
            ->keyBy(fn ($j) => $j->tanggal->toDateString());

        // Hari magang dari semua jadwal sampai hari ini, terbaru dulu.
        $hariMagang = $jadwalList
            ->flatMap(fn ($jadwal) => collect($jadwal->tanggalMagang())->map(fn ($t) => [
                'tanggal' => $t,
                'jadwal' => $jadwal,
                'jurnal' => $jurnal->get($t->toDateString()),
            ]))
            ->unique(fn ($h) => $h['tanggal']->toDateString())
            ->sortByDesc(fn ($h) => $h['tanggal']->timestamp)
            ->values();

        $hariIniMagang = $jadwalList->first(fn ($j) => $j->berlakuPada(today()));

        return view('pkl.jurnal.siswa', [
            'siswa' => $siswa,
            'jadwalList' => $jadwalList,
            'hariMagang' => $hariMagang,
            'hariIniMagang' => $hariIniMagang,
            'jumlahTerisi' => $hariMagang->whereNotNull('jurnal')->count(),
        ]);
    }

    /** Halaman admin/guru: rekap keterisian per siswa + daftar jurnal untuk diperiksa. */
    private function indexPembimbing(Request $request): View
    {
        $q = trim((string) $request->input('q'));
        $tempat = $request->input('tempat');
        $status = $request->input('status');
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');

        // Jadwal magang yang sedang / pernah berjalan (sudah dimulai).
        $jadwalList = JadwalMagang::where('tanggal_mulai', '<=', today())
            ->when($tempat, fn ($x) => $x->where('tempat', $tempat))
            ->orderBy('tempat')->get();

        $siswaPerNis = $this->siswaPerNis($jadwalList->pluck('nis'));

        if ($q !== '') {
            $jadwalList = $jadwalList->filter(function ($j) use ($q, $siswaPerNis) {
                $s = $siswaPerNis->get($j->nis);

                return str_contains(mb_strtolower(($s->nama_siswa ?? '').' '.$j->nis.' '.$j->pembimbing), mb_strtolower($q));
            })->values();
        }

        // Jumlah jurnal per siswa per status, untuk kolom rekap.
        $siswaIds = $jadwalList->map(fn ($j) => optional($siswaPerNis->get($j->nis))->id)->filter()->unique();
        $hitung = JurnalPkl::whereIn('siswa_id', $siswaIds)
            ->selectRaw('siswa_id, jadwal_magang_id, status, count(*) as jumlah')
            ->groupBy('siswa_id', 'jadwal_magang_id', 'status')
            ->get();

        $rekap = $jadwalList->map(function ($j) use ($siswaPerNis, $hitung) {
            $siswa = $siswaPerNis->get($j->nis);
            $baris = $hitung->where('jadwal_magang_id', $j->id)->where('siswa_id', optional($siswa)->id);

            return [
                'jadwal' => $j,
                'siswa' => $siswa,
                'wajib' => count($j->tanggalMagang()),
                'terisi' => $baris->sum('jumlah'),
                'disetujui' => $baris->where('status', JurnalPkl::STATUS_DISETUJUI)->sum('jumlah'),
                'menunggu' => $baris->where('status', JurnalPkl::STATUS_MENUNGGU)->sum('jumlah'),
            ];
        });

        $items = JurnalPkl::with('siswa')
            ->whereIn('siswa_id', $siswaIds)
            ->when($tempat, fn ($x) => $x->where('tempat', $tempat))
            ->when($status, fn ($x) => $x->where('status', $status))
            ->when($dari, fn ($x) => $x->whereDate('tanggal', '>=', $dari))
            ->when($sampai, fn ($x) => $x->whereDate('tanggal', '<=', $sampai))
            ->orderByDesc('tanggal')->orderByDesc('id')
            ->paginate((int) $request->input('limit', 20))
            ->withQueryString();

        return view('pkl.jurnal.index', [
            'rekap' => $rekap,
            'items' => $items,
            'tempatList' => JadwalMagang::distinct()->orderBy('tempat')->pluck('tempat'),
            'filter' => compact('q', 'tempat', 'status', 'dari', 'sampai'),
        ]);
    }

    /** Form isi / ubah jurnal pada satu tanggal magang (siswa). */
    public function form(Request $request): View|RedirectResponse
    {
        $siswa = $this->siswaLogin($request);
        $tanggal = $this->tanggalDariRequest($request);
        $jadwal = $this->jadwalPada($siswa, $tanggal);

        if (! $jadwal) {
            return redirect()->route('pkl.jurnal.index')
                ->with('error', 'Tanggal '.$tanggal->translatedFormat('d F Y').' bukan hari magang Anda.');
        }

        $item = JurnalPkl::where('siswa_id', $siswa->id)->whereDate('tanggal', $tanggal)->first();

        if ($item && ! $item->bisaDiubahSiswa()) {
            return redirect()->route('pkl.jurnal.show', $item)->with('error', 'Jurnal yang sudah disetujui tidak bisa diubah.');
        }

        return view('pkl.jurnal.form', compact('siswa', 'tanggal', 'jadwal', 'item'));
    }

    public function store(Request $request): RedirectResponse
    {
        $siswa = $this->siswaLogin($request);
        $data = $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after:jam_mulai',
            'kegiatan' => 'required|string|max:5000',
            'hasil' => 'nullable|string|max:5000',
            'kendala' => 'nullable|string|max:5000',
            'foto' => 'nullable|image|max:5120',
        ], [], [
            'jam_mulai' => 'Jam Mulai',
            'jam_selesai' => 'Jam Selesai',
            'kegiatan' => 'Kegiatan',
            'hasil' => 'Hasil / Kompetensi',
            'kendala' => 'Kendala',
        ]);

        $tanggal = Carbon::parse($data['tanggal'])->startOfDay();
        $jadwal = $this->jadwalPada($siswa, $tanggal);
        abort_unless($jadwal, 422, 'Tanggal tersebut bukan hari magang Anda.');

        $item = JurnalPkl::where('siswa_id', $siswa->id)->whereDate('tanggal', $tanggal)->first();
        abort_if($item && ! $item->bisaDiubahSiswa(), 403, 'Jurnal yang sudah disetujui tidak bisa diubah.');

        unset($data['foto']);
        if ($request->hasFile('foto')) {
            if ($item?->foto) {
                Storage::disk('public')->delete($item->foto);
            }
            $data['foto'] = $request->file('foto')->store('uploads/jurnal_pkl', 'public');
        }

        $item ??= new JurnalPkl(['siswa_id' => $siswa->id]);
        $item->fill($data + [
            'jadwal_magang_id' => $jadwal->id,
            'tempat' => $jadwal->tempat,
            'tanggal' => $tanggal->toDateString(),
            // Setiap simpan (termasuk perbaikan revisi) kembali menunggu pemeriksaan.
            'status' => JurnalPkl::STATUS_MENUNGGU,
        ]);
        $item->save();

        return redirect()->route('pkl.jurnal.index')->with('success', 'Jurnal PKL tanggal '.$tanggal->translatedFormat('d F Y').' berhasil disimpan.');
    }

    public function show(Request $request, JurnalPkl $jurnal): View
    {
        $this->pastikanBolehLihat($request, $jurnal);
        $jurnal->load(['siswa', 'pemeriksa']);

        return view('pkl.jurnal.show', ['item' => $jurnal, 'jadwal' => $this->jadwalAman($jurnal)]);
    }

    /** Pemeriksaan oleh admin/guru: setujui atau minta revisi, dengan catatan. */
    public function periksa(Request $request, JurnalPkl $jurnal): RedirectResponse
    {
        abort_if($request->user()->isSiswa(), 403);

        $data = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(JurnalPkl::WARNA_STATUS)),
            'catatan_pembimbing' => 'nullable|string|max:2000',
        ]);

        $jurnal->update($data + ['diperiksa_oleh' => $request->user()->id, 'diperiksa_pada' => now()]);

        return back()->with('success', 'Jurnal '.optional($jurnal->siswa)->nama_siswa.' ditandai "'.$data['status'].'".');
    }

    public function destroy(Request $request, JurnalPkl $jurnal): RedirectResponse
    {
        $user = $request->user();
        if ($user->isSiswa()) {
            abort_unless((int) $jurnal->siswa_id === (int) $user->siswa_id && $jurnal->bisaDiubahSiswa(), 403);
        }

        if ($jurnal->foto) {
            Storage::disk('public')->delete($jurnal->foto);
        }
        $jurnal->delete();

        return back()->with('success', 'Jurnal PKL berhasil dihapus.');
    }

    /** Cetak jurnal satu siswa pada satu jadwal magang. */
    public function cetak(Request $request): View
    {
        $user = $request->user();
        $siswaId = $user->isSiswa() ? $user->siswa_id : (int) $request->input('siswa_id');
        $siswa = Siswa::findOrFail($siswaId);

        $jadwal = JadwalMagang::milik($siswa)
            ->when($request->input('jadwal_id'), fn ($x, $id) => $x->whereKey($id))
            ->orderByDesc('tanggal_mulai')->firstOrFail();

        $items = JurnalPkl::where('siswa_id', $siswa->id)
            ->where('jadwal_magang_id', $jadwal->id)
            ->orderBy('tanggal')->get();

        return view('pkl.jurnal.cetak', [
            'siswa' => $siswa->load('rombelSaatIni.kelas'),
            'jadwal' => $jadwal,
            'items' => $items,
            'identitas' => IdentitasSekolah::first(),
        ]);
    }

    // ------------------------------------------------------------------

    private function siswaLogin(Request $request): Siswa
    {
        $user = $request->user();
        abort_unless($user->isSiswa() && $user->siswa_id, 403, 'Menu ini untuk siswa yang terdaftar magang.');

        return Siswa::findOrFail($user->siswa_id);
    }

    private function tanggalDariRequest(Request $request): Carbon
    {
        $tanggal = rescue(fn () => Carbon::parse($request->input('tanggal', today()->toDateString())), today(), false)->startOfDay();

        // Jurnal tidak boleh diisi untuk hari yang belum terjadi.
        return $tanggal->gt(today()) ? today() : $tanggal;
    }

    /** Jadwal magang siswa yang berlaku pada tanggal tsb (periode + hari), atau null. */
    private function jadwalPada(Siswa $siswa, Carbon $tanggal): ?JadwalMagang
    {
        return JadwalMagang::milik($siswa)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->get()
            ->first(fn ($j) => $j->berlakuPada($tanggal));
    }

    /** Jadwal asal jurnal; null bila database Absensi tak terjangkau / jadwal sudah dihapus. */
    private function jadwalAman(JurnalPkl $jurnal): ?JadwalMagang
    {
        return rescue(fn () => JadwalMagang::find($jurnal->jadwal_magang_id), null, false);
    }

    private function pastikanBolehLihat(Request $request, JurnalPkl $jurnal): void
    {
        $user = $request->user();
        abort_if($user->isSiswa() && (int) $jurnal->siswa_id !== (int) $user->siswa_id, 403);
    }

    /** Peta NIS/NISN jadwal → model Siswa datacenter. */
    private function siswaPerNis(Collection $nis): Collection
    {
        $nis = $nis->filter()->unique()->values();
        if ($nis->isEmpty()) {
            return collect();
        }

        $siswa = Siswa::whereIn('nis', $nis)->orWhereIn('nisn', $nis)->get();

        return $nis->mapWithKeys(fn ($n) => [
            $n => $siswa->first(fn ($s) => (string) $s->nis === (string) $n)
                ?? $siswa->first(fn ($s) => (string) $s->nisn === (string) $n),
        ])->filter();
    }
}
