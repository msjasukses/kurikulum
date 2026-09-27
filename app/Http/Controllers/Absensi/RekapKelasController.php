<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Concerns\FilterPenugasanGuru;
use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Support\TahunAjaranTerpilih;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Rekap absensi satu kelas pada rentang tanggal. Bisa disaring per mata
 * pelajaran; bila mata pelajaran dikosongkan, yang dihitung adalah seluruh
 * pertemuan semua mata pelajaran, dengan rincian per mapel di kolom terakhir.
 */
class RekapKelasController extends Controller
{
    use FilterPenugasanGuru;

    public function index(Request $request): View
    {
        // Daftar kelas mengikuti tahun ajaran yang dipilih di topbar.
        $tahunAjaranId = app(TahunAjaranTerpilih::class)->id();
        $kelasIds = $this->kelasIdsGuru();
        $mapelIds = $this->mapelIdsGuru();

        $kelasList = Kelas::when($tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->when($kelasIds !== null, fn ($q) => $q->whereIn('id', $kelasIds))
            ->orderBy('nama_rombel')->get();

        $mapelList = MataPelajaran::when($mapelIds !== null, fn ($q) => $q->whereIn('id', $mapelIds))
            ->orderBy('nama_mapel')->get();

        $kelasId = $request->input('kelas_id');
        $mapelId = $request->input('mata_pelajaran_id');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $rekap = collect();

        if ($kelasId && $tanggalMulai && $tanggalSelesai) {
            // Siswa tidak lagi punya kolom kelas_id langsung; penempatan
            // kelasnya dicek lewat relasi rombelSaatIni (tabel siswa_rombel).
            $siswaList = Siswa::whereHas('rombelSaatIni', fn ($q) => $q->where('rombongan_belajar_id', $kelasId))
                ->orderBy('nama_siswa')
                ->get();

            $absensi = AbsensiSiswa::with('mataPelajaran')
                ->where('kelas_id', $kelasId)
                ->when($mapelId, fn ($q) => $q->where('mata_pelajaran_id', $mapelId))
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->get()
                ->groupBy('siswa_id');

            foreach ($siswaList as $siswa) {
                $records = $absensi->get($siswa->id, collect());

                // Rincian per mata pelajaran, mis. "Matematika: 8/10 hadir".
                $perMapel = $records->groupBy('mata_pelajaran_id')
                    ->map(fn ($baris) => [
                        'nama' => optional($baris->first()->mataPelajaran)->nama_mapel ?? 'Tanpa mapel',
                        'hadir' => $baris->where('status', 'Hadir')->count(),
                        'total' => $baris->count(),
                    ])
                    ->sortBy('nama')
                    ->values();

                $rekap->push([
                    'siswa' => $siswa,
                    'hadir' => $records->where('status', 'Hadir')->count(),
                    'izin' => $records->where('status', 'Izin')->count(),
                    'sakit' => $records->where('status', 'Sakit')->count(),
                    'alpa' => $records->where('status', 'Alpa')->count(),
                    'total' => $records->count(),
                    'per_mapel' => $perMapel,
                ]);
            }
        }

        return view('absensi.rekap-kelas', [
            'kelasList' => $kelasList,
            'mapelList' => $mapelList,
            'kelasId' => $kelasId,
            'mapelId' => $mapelId,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'rekap' => $rekap,
        ]);
    }
}
