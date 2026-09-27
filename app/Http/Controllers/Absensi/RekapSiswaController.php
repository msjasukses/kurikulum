<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Rekap absensi seorang siswa: daftar kehadiran per mata pelajaran pada
 * rentang tanggal, plus ringkasan jumlah pertemuan tiap mata pelajaran.
 */
class RekapSiswaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isSiswa = $user->isSiswa();

        $siswaList = $isSiswa ? collect() : Siswa::orderBy('nama_siswa')->get();
        $siswaId = $isSiswa ? $user->siswa_id : $request->input('siswa_id');
        $mapelId = $request->input('mata_pelajaran_id');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $records = collect();
        $ringkasan = collect();

        if ($siswaId && $tanggalMulai && $tanggalSelesai) {
            $records = AbsensiSiswa::with('mataPelajaran')
                ->where('siswa_id', $siswaId)
                ->when($mapelId, fn ($q) => $q->where('mata_pelajaran_id', $mapelId))
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->orderBy('tanggal')
                ->get();

            // Ringkasan per mata pelajaran supaya mudah dilihat mapel mana
            // yang kehadirannya kurang.
            $ringkasan = $records->groupBy('mata_pelajaran_id')
                ->map(fn ($baris) => [
                    'nama' => optional($baris->first()->mataPelajaran)->nama_mapel ?? 'Tanpa mapel',
                    'hadir' => $baris->where('status', 'Hadir')->count(),
                    'izin' => $baris->where('status', 'Izin')->count(),
                    'sakit' => $baris->where('status', 'Sakit')->count(),
                    'alpa' => $baris->where('status', 'Alpa')->count(),
                    'total' => $baris->count(),
                ])
                ->sortBy('nama')
                ->values();
        }

        return view('absensi.rekap-siswa', [
            'siswaList' => $siswaList,
            'mapelList' => MataPelajaran::orderBy('nama_mapel')->get(),
            'siswaId' => $siswaId,
            'mapelId' => $mapelId,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'records' => $records,
            'ringkasan' => $ringkasan,
            'isSiswa' => $isSiswa,
        ]);
    }
}
