<?php

namespace App\Http\Controllers;

use App\Models\AgendaMengajar;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\Tugas;
use App\Support\TahunAjaranTerpilih;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tahunAjaran = app(TahunAjaranTerpilih::class);

        $stats = [
            // Datacenter tidak punya tabel pegawai umum, jadi statistik ini
            // sekarang menghitung data guru (App\Models\Guru).
            'pegawai' => Guru::count(),
            // Siswa dihitung dari penempatan kelas pada tahun ajaran terpilih
            // (relasi rombelSaatIni sudah mengikuti pilihan di topbar).
            'siswa' => Siswa::whereHas('rombelSaatIni')->count(),
            'mata_pelajaran' => MataPelajaran::count(),
            'guru_mapel' => GuruMapel::when($tahunAjaran->id(), fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id()))->count(),
        ];

        // Agenda mengajar yang diisi hari ini (untuk tabel di dashboard admin).
        $agendaHariIni = collect();
        if ($user->isAdmin()) {
            $agendaHariIni = AgendaMengajar::with(['guru', 'kelas', 'mataPelajaran'])
                ->whereDate('waktu_pengisian', now()->toDateString())
                ->when($tahunAjaran->nama(), fn ($q, $nama) => $q->where(
                    fn ($x) => $x->where('tahun_ajaran', $nama)->orWhereNull('tahun_ajaran')
                ))
                ->orderByDesc('waktu_pengisian')
                ->get();
        }

        $tugasTerbaru = collect();
        if (! $user->isAdmin()) {
            $query = Tugas::with(['mataPelajaran', 'kelas'])->latest();
            if ($user->isSiswa() && $user->siswa) {
                // Siswa tidak lagi punya kolom kelas_id langsung; kelas
                // terkininya diambil lewat relasi rombelSaatIni (siswa_rombel).
                $kelasId = optional($user->siswa->rombelSaatIni)->rombongan_belajar_id;
                $query->where('kelas_id', $kelasId);
            } elseif ($user->isGuru() && $user->pegawai) {
                $query->where('pegawai_id', $user->pegawai->id);
            }
            $tugasTerbaru = $query->limit(5)->get();
        }

        return view('dashboard.index', compact('stats', 'tugasTerbaru', 'agendaHariIni') + [
            'tahunAjaran' => $tahunAjaran->nama(),
        ]);
    }
}
