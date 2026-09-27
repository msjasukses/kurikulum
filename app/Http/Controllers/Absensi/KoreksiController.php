<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Concerns\FilterPenugasanGuru;
use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Support\TahunAjaranTerpilih;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Input & koreksi absensi siswa per mata pelajaran: guru memilih kelas,
 * tanggal, dan mata pelajaran, lalu mengisi kehadiran seluruh siswa kelas
 * itu untuk jam pelajaran tersebut. Satu siswa bisa punya beberapa baris
 * absensi dalam sehari — satu untuk tiap mata pelajaran.
 */
class KoreksiController extends Controller
{
    use FilterPenugasanGuru;

    public function index(Request $request): View
    {
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
        $tanggal = $request->input('tanggal');

        $rows = collect();

        if ($kelasId && $mapelId && $tanggal) {
            // Siswa tidak lagi punya kolom kelas_id langsung; penempatan
            // kelasnya dicek lewat relasi rombelSaatIni (tabel siswa_rombel).
            $siswaList = Siswa::whereHas('rombelSaatIni', fn ($q) => $q->where('rombongan_belajar_id', $kelasId))
                ->orderBy('nama_siswa')
                ->get();

            $existing = AbsensiSiswa::where('kelas_id', $kelasId)
                ->where('mata_pelajaran_id', $mapelId)
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
            'mapelList' => $mapelList,
            'kelasId' => $kelasId,
            'mapelId' => $mapelId,
            'tanggal' => $tanggal,
            'rows' => $rows,
            'sudahDipilih' => (bool) ($kelasId && $mapelId && $tanggal),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => 'required|integer',
            'mata_pelajaran_id' => 'required|integer',
            'tanggal' => 'required|date',
            'siswa_id' => 'required|array',
            'status' => 'required|array',
            'keterangan' => 'nullable|array',
        ], [], [
            'mata_pelajaran_id' => 'Mata Pelajaran',
        ]);

        foreach ($validated['siswa_id'] as $i => $siswaId) {
            AbsensiSiswa::updateOrCreate(
                [
                    'siswa_id' => $siswaId,
                    'tanggal' => $validated['tanggal'],
                    'mata_pelajaran_id' => $validated['mata_pelajaran_id'],
                ],
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
            'mata_pelajaran_id' => $validated['mata_pelajaran_id'],
            'tanggal' => $validated['tanggal'],
        ])->with('success', 'Absensi mata pelajaran berhasil disimpan.');
    }
}
