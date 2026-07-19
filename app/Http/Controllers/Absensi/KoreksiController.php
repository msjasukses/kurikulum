<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KoreksiController extends Controller
{
    public function index(Request $request): View
    {
        $kelasList = Kelas::orderBy('nama_rombel')->get();
        $kelasId = $request->input('kelas_id');
        $tanggal = $request->input('tanggal');

        $rows = collect();

        if ($kelasId && $tanggal) {
            // Siswa tidak lagi punya kolom kelas_id langsung; penempatan
            // kelasnya dicek lewat relasi rombelSaatIni (tabel siswa_rombel).
            $siswaList = Siswa::whereHas('rombelSaatIni', fn ($q) => $q->where('rombongan_belajar_id', $kelasId))
                ->orderBy('nama_siswa')
                ->get();
            $existing = AbsensiSiswa::where('kelas_id', $kelasId)
                ->where('tanggal', $tanggal)
                ->get()
                ->keyBy('siswa_id');

            foreach ($siswaList as $siswa) {
                $rows->push([
                    'siswa' => $siswa,
                    'status' => optional($existing->get($siswa->id))->status ?? 'Hadir',
                    'keterangan' => optional($existing->get($siswa->id))->keterangan,
                ]);
            }
        }

        return view('absensi.koreksi', [
            'kelasList' => $kelasList,
            'kelasId' => $kelasId,
            'tanggal' => $tanggal,
            'rows' => $rows,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => 'required|integer',
            'tanggal' => 'required|date',
            'siswa_id' => 'required|array',
            'status' => 'required|array',
            'keterangan' => 'nullable|array',
        ]);

        foreach ($validated['siswa_id'] as $i => $siswaId) {
            AbsensiSiswa::updateOrCreate(
                ['siswa_id' => $siswaId, 'tanggal' => $validated['tanggal']],
                [
                    'kelas_id' => $validated['kelas_id'],
                    'status' => $validated['status'][$i] ?? 'Hadir',
                    'keterangan' => $validated['keterangan'][$i] ?? null,
                    'dicatat_oleh' => $request->user()->id,
                ]
            );
        }

        return redirect()->route('absensi.koreksi.index', [
            'kelas_id' => $validated['kelas_id'],
            'tanggal' => $validated['tanggal'],
        ])->with('success', 'Absensi berhasil disimpan.');
    }
}
