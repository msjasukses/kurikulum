<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekapSiswaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isSiswa = $user->isSiswa();

        $siswaList = $isSiswa ? collect() : Siswa::orderBy('nama_siswa')->get();
        $siswaId = $isSiswa ? $user->siswa_id : $request->input('siswa_id');
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $records = collect();

        if ($siswaId && $tanggalMulai && $tanggalSelesai) {
            $records = AbsensiSiswa::where('siswa_id', $siswaId)
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->orderBy('tanggal')
                ->get();
        }

        return view('absensi.rekap-siswa', [
            'siswaList' => $siswaList,
            'siswaId' => $siswaId,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'records' => $records,
            'isSiswa' => $isSiswa,
        ]);
    }
}
