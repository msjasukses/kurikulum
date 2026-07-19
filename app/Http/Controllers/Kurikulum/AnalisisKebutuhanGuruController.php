<?php

namespace App\Http\Controllers\Kurikulum;

use App\Exports\AnalisisKebutuhanGuruExport;
use App\Http\Controllers\Controller;
use App\Models\AlokasiJamMapel;
use App\Models\Guru;
use App\Models\IdentitasSekolah;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\TingkatKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Analisis Kebutuhan Guru: membandingkan total jam dari Alokasi Jam Mapel
 * (per tingkat) dengan jumlah guru pengampu yang ada — diambil dari field
 * "Guru Mata Pelajaran" (guru.mata_pelajaran_id) di Data Guru datacenter —
 * per status kepegawaian, berdasarkan beban standar JP/minggu yang bisa diubah.
 */
class AnalisisKebutuhanGuruController extends Controller
{
    /** Beban standar default: jam wajib mengajar per guru per minggu. */
    const JAM_WAJIB_PER_GURU = 24;

    public function index(Request $request): View
    {
        return view('kurikulum.analisis-guru', $this->buildData($request));
    }

    public function excel(Request $request)
    {
        $data = $this->buildData($request);

        return Excel::download(
            new AnalisisKebutuhanGuruExport($data),
            'analisis-kebutuhan-guru-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    private function buildData(Request $request): array
    {
        $beban = max(1, (int) $request->input('beban', self::JAM_WAJIB_PER_GURU));

        $identitas = IdentitasSekolah::first();
        $tahunAktif = TahunAjaran::where('is_aktif', true)->first();
        $tingkatList = TingkatKelas::orderBy('urutan')->get();

        // Alokasi tahun ajaran aktif. Bila satu mapel+tingkat punya alokasi di
        // dua semester, diambil total terbesar (beban mingguan berjalan),
        // bukan dijumlahkan, supaya tidak terhitung ganda.
        $alokasi = AlokasiJamMapel::query()
            ->when($tahunAktif, fn ($q) => $q->where('tahun_ajaran', $tahunAktif->nama_tahun_ajaran))
            ->get();

        $mapelIds = $alokasi->pluck('mata_pelajaran_id')->unique();
        $mapelList = MataPelajaran::whereIn('id', $mapelIds)->orderBy('nama_mapel')->get();

        // Guru pengampu per mapel diambil dari kolom "mata_pelajaran_id" di
        // tabel guru (field "Guru Mata Pelajaran" pada Data Guru datacenter),
        // dikelompokkan per status kepegawaian. Hanya guru aktif yang dihitung.
        $guruExisting = Guru::query()
            ->whereIn('mata_pelajaran_id', $mapelIds)
            ->where('is_aktif', true)
            ->get(['id', 'mata_pelajaran_id', 'status_kepegawaian']);

        $hasil = collect();
        foreach ($mapelList as $mapel) {
            $alokasiMapel = $alokasi->where('mata_pelajaran_id', $mapel->id);

            $perTingkat = [];
            $totalJam = 0;
            foreach ($tingkatList as $tingkat) {
                $terbesar = $alokasiMapel->where('tingkat_kelas_id', $tingkat->id)
                    ->sortByDesc(fn ($a) => $a->total_jp)
                    ->first();
                $perTingkat[$tingkat->id] = [
                    'jam' => $terbesar->jumlah_jam_per_minggu ?? 0,
                    'kls' => $terbesar->jumlah_kelas ?? 0,
                    'total' => $terbesar->total_jp ?? 0,
                ];
                $totalJam += $perTingkat[$tingkat->id]['total'];
            }

            $kebutuhan = $totalJam > 0 ? (int) ceil($totalJam / $beban) : 0;

            $existing = ['pns' => 0, 'pppk' => 0, 'kki' => 0, 'hon' => 0];
            foreach ($guruExisting->where('mata_pelajaran_id', $mapel->id) as $g) {
                $existing[$this->kelompokStatus($g->status_kepegawaian)]++;
            }
            $totalExisting = array_sum($existing);

            $hasil->push([
                'mapel' => $mapel,
                'per_tingkat' => $perTingkat,
                'total_jam' => $totalJam,
                'kebutuhan' => $kebutuhan,
                'existing' => $existing,
                'total_existing' => $totalExisting,
                'lebih' => max($totalExisting - $kebutuhan, 0),
                'kurang' => max($kebutuhan - $totalExisting, 0),
            ]);
        }

        return [
            'beban' => $beban,
            'identitas' => $identitas,
            'tahunAktif' => $tahunAktif,
            'tingkatList' => $tingkatList,
            'hasil' => $hasil,
        ];
    }

    /** Kelompokkan teks status kepegawaian datacenter ke kolom PNS/PPPK/KKI/Hon. */
    private function kelompokStatus(?string $status): string
    {
        $s = Str::lower($status ?? '');

        return match (true) {
            Str::contains($s, ['pppk', 'p3k']) => 'pppk',
            Str::contains($s, 'pns') => 'pns',
            Str::contains($s, 'kki') => 'kki',
            default => 'hon',
        };
    }
}
