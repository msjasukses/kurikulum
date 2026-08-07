<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Support\TahunAjaranTerpilih;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekapKelasController extends Controller
{
    public function index(Request $request): View
    {
        // Daftar kelas mengikuti tahun ajaran yang dipilih di topbar.
        $tahunAjaranId = app(TahunAjaranTerpilih::class)->id();

        $kelasList = Kelas::when($tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderBy('nama_rombel')->get();
        $kelasId = $request->input('kelas_id');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $rekap = collect();

        if ($kelasId && $tanggalMulai && $tanggalSelesai) {
            // Siswa tidak lagi punya kolom kelas_id langsung; penempatan
            // kelasnya dicek lewat relasi rombelSaatIni (tabel siswa_rombel).
            $siswaList = Siswa::whereHas('rombelSaatIni', fn ($q) => $q->where('rombongan_belajar_id', $kelasId))
                ->orderBy('nama_siswa')
                ->get();

            $absensi = AbsensiSiswa::where('kelas_id', $kelasId)
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->get()
                ->groupBy('siswa_id');

            foreach ($siswaList as $siswa) {
                $records = $absensi->get($siswa->id, collect());
                $rekap->push([
                    'siswa' => $siswa,
                    'hadir' => $records->where('status', 'Hadir')->count(),
                    'izin' => $records->where('status', 'Izin')->count(),
                    'sakit' => $records->where('status', 'Sakit')->count(),
                    'alpa' => $records->where('status', 'Alpa')->count(),
                    'total' => $records->count(),
                ]);
            }
        }

        return view('absensi.rekap-kelas', [
            'kelasList' => $kelasList,
            'kelasId' => $kelasId,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'rekap' => $rekap,
        ]);
    }
}
