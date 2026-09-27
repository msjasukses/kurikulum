<?php

namespace App\Http\Controllers\RuangBelajar;

use App\Http\Controllers\Controller;
use App\Models\PengumpulanTugas;
use App\Models\Siswa;
use App\Models\SiswaRombel;
use App\Models\Tugas;
use App\Support\TahunAjaranTerpilih;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Pengumpulan tugas: siswa mengunggah jawaban atas tugas yang dibuat gurunya,
 * guru/admin melihat daftar pengumpulan per tugas lalu memberi nilai.
 */
class PengumpulanTugasController extends Controller
{
    private const FOLDER = 'uploads/pengumpulan_tugas';

    /** Halaman siswa: form kumpul/ubah jawaban untuk satu tugas. */
    public function form(int $tugasId): View
    {
        $user = auth()->user();
        abort_unless($user->isSiswa(), 403);

        $tugas = Tugas::with(['mataPelajaran', 'kelas', 'pegawai'])->findOrFail($tugasId);
        $this->pastikanTugasKelasSiswa($tugas, $user->siswa_id);

        return view('ruangbelajar.pengumpulan-form', [
            'tugas' => $tugas,
            'pengumpulan' => PengumpulanTugas::where('tugas_id', $tugas->id)
                ->where('siswa_id', $user->siswa_id)->first(),
            'lewatBatas' => $this->lewatBatas($tugas),
        ]);
    }

    /** Simpan jawaban siswa (buat baru atau perbarui yang belum dinilai). */
    public function store(Request $request, int $tugasId): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->isSiswa(), 403);

        $tugas = Tugas::findOrFail($tugasId);
        $this->pastikanTugasKelasSiswa($tugas, $user->siswa_id);

        $data = $request->validate([
            'catatan' => ['nullable', 'string', 'max:5000'],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,jpg,jpeg,png'],
        ], [], [
            'catatan' => 'Catatan/Jawaban',
            'file' => 'File Jawaban',
        ]);

        $pengumpulan = PengumpulanTugas::firstOrNew([
            'tugas_id' => $tugas->id,
            'siswa_id' => $user->siswa_id,
        ]);

        if ($pengumpulan->sudahDinilai()) {
            return back()->with('error', 'Tugas ini sudah dinilai guru, jawabannya tidak bisa diubah lagi.');
        }

        if (blank($data['catatan'] ?? null) && ! $request->hasFile('file') && ! $pengumpulan->exists) {
            return back()->withInput()->withErrors(['catatan' => 'Isi catatan jawaban atau unggah file terlebih dahulu.']);
        }

        if ($request->hasFile('file')) {
            if ($pengumpulan->file) {
                Storage::disk('public')->delete($pengumpulan->file);
            }
            $pengumpulan->file = $request->file('file')->store(self::FOLDER, 'public');
        }

        $pengumpulan->catatan = $data['catatan'] ?? null;
        $pengumpulan->dikumpulkan_pada = now();
        $pengumpulan->terlambat = $this->lewatBatas($tugas);
        $pengumpulan->save();

        return redirect()->route('ruangbelajar.tugas.index')
            ->with('success', $pengumpulan->terlambat
                ? 'Tugas berhasil dikumpulkan, tetapi tercatat melewati batas waktu.'
                : 'Tugas berhasil dikumpulkan.');
    }

    /** Halaman guru/admin: daftar siswa sekelas beserta status pengumpulannya. */
    public function daftar(int $tugasId): View
    {
        $user = auth()->user();
        abort_if($user->isSiswa(), 403);

        $tugas = Tugas::with(['mataPelajaran', 'kelas', 'pegawai'])->findOrFail($tugasId);
        $this->pastikanTugasMilikGuru($tugas);

        // Semua siswa di kelas tugas ini pada tahun ajaran kelas tersebut,
        // supaya yang belum mengumpulkan pun tetap terlihat.
        $siswaIds = SiswaRombel::where('rombongan_belajar_id', $tugas->kelas_id)
            ->when($tugas->kelas?->tahun_ajaran_id, fn ($q, $ta) => $q->where('tahun_ajaran_id', $ta))
            ->pluck('siswa_id');

        $pengumpulan = PengumpulanTugas::where('tugas_id', $tugas->id)->get()->keyBy('siswa_id');

        $baris = Siswa::whereIn('id', $siswaIds)->orderBy('nama_siswa')->get()
            ->map(fn ($siswa) => [
                'siswa' => $siswa,
                'pengumpulan' => $pengumpulan->get($siswa->id),
            ]);

        return view('ruangbelajar.pengumpulan-daftar', [
            'tugas' => $tugas,
            'baris' => $baris,
            'jumlahKumpul' => $pengumpulan->count(),
            'jumlahSiswa' => $baris->count(),
        ]);
    }

    /** Guru menyimpan nilai & umpan balik untuk satu pengumpulan. */
    public function nilai(Request $request, int $pengumpulanId): RedirectResponse
    {
        $user = auth()->user();
        abort_if($user->isSiswa(), 403);

        $pengumpulan = PengumpulanTugas::with('tugas')->findOrFail($pengumpulanId);
        $this->pastikanTugasMilikGuru($pengumpulan->tugas);

        $data = $request->validate([
            'nilai' => ['nullable', 'integer', 'min:0', 'max:100'],
            'umpan_balik' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'nilai' => 'Nilai',
            'umpan_balik' => 'Umpan Balik',
        ]);

        $pengumpulan->nilai = $data['nilai'] ?? null;
        $pengumpulan->umpan_balik = $data['umpan_balik'] ?? null;
        $pengumpulan->dinilai_pada = $pengumpulan->nilai === null ? null : now();
        $pengumpulan->save();

        return back()->with('success', 'Penilaian berhasil disimpan.');
    }

    /** Batas akhir dihitung sampai akhir hari tanggal selesai. */
    private function lewatBatas(Tugas $tugas): bool
    {
        return $tugas->tanggal_selesai !== null && now()->gt($tugas->tanggal_selesai->endOfDay());
    }

    /** Siswa hanya boleh mengumpulkan tugas untuk kelasnya sendiri. */
    private function pastikanTugasKelasSiswa(Tugas $tugas, ?int $siswaId): void
    {
        abort_if($siswaId === null, 403, 'Akun Anda belum tertaut ke data siswa.');

        $terdaftar = SiswaRombel::where('siswa_id', $siswaId)
            ->where('rombongan_belajar_id', $tugas->kelas_id)
            ->exists();

        abort_unless($terdaftar, 403, 'Tugas ini bukan untuk kelas Anda.');
    }

    /** Guru hanya boleh melihat/menilai pengumpulan tugas buatannya sendiri. */
    private function pastikanTugasMilikGuru(Tugas $tugas): void
    {
        $user = auth()->user();

        if ($user->isGuru() && $user->guru_id) {
            abort_unless((int) $tugas->pegawai_id === (int) $user->guru_id, 403, 'Tugas ini bukan milik Anda.');
        }
    }
}
