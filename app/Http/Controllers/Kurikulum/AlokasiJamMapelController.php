<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\AlokasiJamMapel;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\TingkatKelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Menu Alokasi Jam Mapel: form tambah/edit + daftar alokasi dalam satu
 * halaman. Data master (tingkat, mapel, tahun ajaran, jumlah rombel) diambil
 * dari database datacenter (read-only); alokasinya sendiri disimpan di
 * database lokal.
 */
class AlokasiJamMapelController extends Controller
{
    public function index(Request $request): View
    {
        $tahunList = TahunAjaran::orderByDesc('nama_tahun_ajaran')->get();
        $tahunAktif = $tahunList->firstWhere('is_aktif', true);

        $tingkatList = TingkatKelas::orderBy('urutan')->get();
        $mapelList = MataPelajaran::orderBy('nama_mapel')->get();

        // Jumlah rombel per tingkat dari datacenter (rombongan_belajar.tingkat
        // berisi angka yang berkorespondensi dengan tingkat_kelas.nomor),
        // dipakai untuk mengisi otomatis kolom "Jumlah Kelas" di form.
        $jumlahKelasPerTingkat = Kelas::selectRaw('tingkat, count(*) as jumlah')
            ->groupBy('tingkat')
            ->pluck('jumlah', 'tingkat');
        $tingkatList->each(function ($t) use ($jumlahKelasPerTingkat) {
            $t->jumlah_rombel = (int) ($jumlahKelasPerTingkat[$t->nomor] ?? 0);
        });

        // Filter daftar; default tahun mengikuti tahun ajaran aktif.
        $filterTahun = $request->input('tahun', optional($tahunAktif)->nama_tahun_ajaran);
        $filterSemester = $request->input('semester', '');
        $filterTingkat = $request->input('tingkat', '');

        $items = AlokasiJamMapel::with(['tingkatKelas', 'mataPelajaran'])
            ->when($filterTahun, fn ($q) => $q->where('tahun_ajaran', $filterTahun))
            ->when($filterSemester, fn ($q) => $q->where('semester', $filterSemester))
            ->when($filterTingkat, fn ($q) => $q->where('tingkat_kelas_id', $filterTingkat))
            ->orderBy('tingkat_kelas_id')
            ->orderBy('semester')
            ->get()
            ->sortBy(fn ($a) => optional($a->mataPelajaran)->nama_mapel)
            ->values();

        $editItem = null;
        if ($request->filled('edit')) {
            $editItem = AlokasiJamMapel::find($request->input('edit'));
        }

        return view('kurikulum.alokasi-jam', [
            'tahunList' => $tahunList,
            'tahunAktif' => $tahunAktif,
            'tingkatList' => $tingkatList,
            'mapelList' => $mapelList,
            'items' => $items,
            'totalJp' => $items->sum(fn ($a) => $a->total_jp),
            'filterTahun' => $filterTahun,
            'filterSemester' => $filterSemester,
            'filterTingkat' => $filterTingkat,
            'editItem' => $editItem,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($this->isDuplicate($data)) {
            return back()->withInput()
                ->with('error', 'Alokasi untuk kombinasi tingkat, mapel, semester, dan tahun ajaran tersebut sudah ada.');
        }

        AlokasiJamMapel::create($data);

        return redirect()
            ->route('kurikulum.alokasi-jam.index', $this->filterParams($data))
            ->with('success', 'Alokasi jam berhasil disimpan.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $alokasi = AlokasiJamMapel::findOrFail($id);
        $data = $this->validated($request);

        if ($this->isDuplicate($data, $alokasi->id)) {
            return back()->withInput()
                ->with('error', 'Alokasi untuk kombinasi tingkat, mapel, semester, dan tahun ajaran tersebut sudah ada.');
        }

        $alokasi->update($data);

        return redirect()
            ->route('kurikulum.alokasi-jam.index', $this->filterParams($data))
            ->with('success', 'Alokasi jam berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        AlokasiJamMapel::findOrFail($id)->delete();

        return back()->with('success', 'Alokasi jam berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'tingkat_kelas_id' => 'required|integer',
            'mata_pelajaran_id' => 'required|integer',
            'jumlah_kelas' => 'required|integer|min:0',
            'jumlah_jam_per_minggu' => 'required|integer|min:1',
            'semester' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:20',
        ], [], [
            'tingkat_kelas_id' => 'Tingkat Kelas',
            'mata_pelajaran_id' => 'Mata Pelajaran',
            'jumlah_kelas' => 'Jumlah Kelas',
            'jumlah_jam_per_minggu' => 'Jam (JP) / Minggu',
        ]);
    }

    private function isDuplicate(array $data, ?int $ignoreId = null): bool
    {
        return AlokasiJamMapel::where('tingkat_kelas_id', $data['tingkat_kelas_id'])
            ->where('mata_pelajaran_id', $data['mata_pelajaran_id'])
            ->where('semester', $data['semester'])
            ->where('tahun_ajaran', $data['tahun_ajaran'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }

    /** Setelah simpan, arahkan filter daftar ke tahun & semester yang diinput. */
    private function filterParams(array $data): array
    {
        return [
            'tahun' => $data['tahun_ajaran'],
            'semester' => $data['semester'],
        ];
    }
}
