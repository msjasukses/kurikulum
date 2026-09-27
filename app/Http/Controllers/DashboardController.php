<?php

namespace App\Http\Controllers;

use App\Models\AgendaMengajar;
use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\MataPelajaran;
use App\Models\PengumpulanTugas;
use App\Models\Siswa;
use App\Models\SiswaRombel;
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
        // Status pengumpulan tiap tugas untuk siswa: tugas_id => baris pengumpulan.
        $pengumpulanSiswa = collect();
        $ringkasanTugas = ['belum' => 0, 'dikumpulkan' => 0, 'dinilai' => 0];

        if (! $user->isAdmin()) {
            $query = Tugas::with(['mataPelajaran', 'kelas'])->latest();

            if ($user->isSiswa()) {
                // Siswa tidak punya kolom kelas_id langsung; kelasnya diambil
                // dari penempatan rombel pada tahun ajaran yang dipilih.
                $kelasIds = SiswaRombel::kelasIdsSiswa($user->siswa_id, $tahunAjaran->id());
                $query->whereIn('kelas_id', $kelasIds ?: [0]);

                // Ringkasan dihitung dari seluruh tugas kelasnya, bukan hanya
                // lima yang tampil di tabel.
                $semuaTugasIds = Tugas::whereIn('kelas_id', $kelasIds ?: [0])->pluck('id');
                $pengumpulanSiswa = PengumpulanTugas::where('siswa_id', $user->siswa_id)
                    ->whereIn('tugas_id', $semuaTugasIds)
                    ->get()
                    ->keyBy('tugas_id');

                $ringkasanTugas = [
                    'belum' => $semuaTugasIds->count() - $pengumpulanSiswa->count(),
                    'dikumpulkan' => $pengumpulanSiswa->count(),
                    'dinilai' => $pengumpulanSiswa->filter(fn ($p) => $p->sudahDinilai())->count(),
                ];
            } elseif ($user->isGuru()) {
                // Kolom pegawai_id pada tabel tugas berisi id guru dari
                // datacenter (lihat RuangBelajar\TugasController), bukan id
                // tabel pegawai lokal. Akun guru lama yang masih tertaut ke
                // pegawai_id tetap dilayani lewat fallback di bawah.
                $guruId = $user->guru_id ?: optional($user->pegawai)->id;
                // Tanpa penanda guru, jangan tampilkan tugas milik guru lain.
                $query->where('pegawai_id', $guruId ?? 0);
            }

            $tugasTerbaru = $query->limit(5)->get();
        }

        return view('dashboard.index', compact('stats', 'tugasTerbaru', 'agendaHariIni', 'pengumpulanSiswa', 'ringkasanTugas') + [
            'tahunAjaran' => $tahunAjaran->nama(),
        ]);
    }
}
