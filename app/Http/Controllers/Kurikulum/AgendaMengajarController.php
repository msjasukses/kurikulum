<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Models\AgendaMengajar;
use App\Models\Guru;
use App\Models\JadwalMengajar;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\ModulAjar;
use App\Models\SiswaRombel;
use App\Support\TahunAjaranTerpilih;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Menu Agenda Mengajar Guru: jurnal pengisian agenda mengajar harian.
 * Agenda disimpan di database lokal "kurikulum"; data master (guru, kelas,
 * mapel, siswa, jadwal) dibaca dari database datacenter / master kurikulum.
 */
class AgendaMengajarController extends Controller
{
    private const HARI = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 0 => 'Minggu'];

    public function index(Request $request): View
    {
        $q = trim((string) $request->input('q'));

        $tahunAjaran = app(TahunAjaranTerpilih::class)->nama();

        $items = AgendaMengajar::with(['guru', 'kelas', 'mataPelajaran', 'modulAjar'])
            // Agenda lama yang belum berlabel tahun ajaran tetap ditampilkan.
            ->when($tahunAjaran, fn ($query) => $query->where(
                fn ($x) => $x->where('tahun_ajaran', $tahunAjaran)->orWhereNull('tahun_ajaran')
            ))
            ->when($q, function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('materi', 'like', "%{$q}%")
                        ->orWhere('catatan', 'like', "%{$q}%")
                        ->orWhere('siswa_absen', 'like', "%{$q}%")
                        ->orWhereIn('guru_id', Guru::where('nama_ptk', 'like', "%{$q}%")
                            ->orWhere('nip', 'like', "%{$q}%")->pluck('id'));
                });
            })
            ->orderByDesc('waktu_pengisian')
            ->paginate((int) $request->input('limit', 20))
            ->withQueryString();

        return view('kurikulum.agenda-mengajar.index', compact('items', 'q'));
    }

    public function create(Request $request): View
    {
        return view('kurikulum.agenda-mengajar.form', $this->formData() + ['item' => null]);
    }

    public function edit(int $id): View
    {
        $item = AgendaMengajar::findOrFail($id);

        return view('kurikulum.agenda-mengajar.form', $this->formData() + ['item' => $item]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['waktu_pengisian'] = now();
        $data['status'] = AgendaMengajar::STATUS_DITINJAU;
        $data['tahun_ajaran'] = app(TahunAjaranTerpilih::class)->nama();
        $data['photo'] = $this->simpanPhoto($request);

        AgendaMengajar::create($data);

        return redirect()->route('kurikulum.agenda.index')
            ->with('success', 'Agenda mengajar berhasil disimpan dan menunggu validasi.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = AgendaMengajar::findOrFail($id);
        $data = $this->validated($request);

        if ($photo = $this->simpanPhoto($request)) {
            $data['photo'] = $photo;
        }

        $item->update($data);

        return redirect()->route('kurikulum.agenda.index')
            ->with('success', 'Agenda mengajar berhasil diperbarui.');
    }

    /** Validasi agenda oleh admin: Disetujui / Ditolak / Sedang Ditinjau. */
    public function setStatus(Request $request, int $id): RedirectResponse
    {
        abort_unless(auth()->user() && auth()->user()->isAdmin(), 403);

        $request->validate(['status' => 'required|in:'.implode(',', [
            AgendaMengajar::STATUS_DITINJAU,
            AgendaMengajar::STATUS_DISETUJUI,
            AgendaMengajar::STATUS_DITOLAK,
        ])]);

        AgendaMengajar::findOrFail($id)->update(['status' => $request->input('status')]);

        return back()->with('success', 'Status validasi agenda diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        AgendaMengajar::findOrFail($id)->delete();

        return back()->with('success', 'Agenda mengajar berhasil dihapus.');
    }

    /** Data master untuk form pengisian agenda. */
    private function formData(): array
    {
        $hariIni = self::HARI[now()->dayOfWeek];
        $tahunAktif = app(TahunAjaranTerpilih::class)->terpilih();

        // Jadwal mengajar hari ini — dipakai autofill jam/kelas/mapel di form.
        $jadwalHariIni = JadwalMengajar::with(['guru', 'kelas', 'mataPelajaran', 'jamMengajar'])
            ->where('hari', $hariIni)
            ->when($tahunAktif, fn ($q) => $q->where('tahun_ajaran', $tahunAktif->nama_tahun_ajaran))
            ->get()
            ->sortBy(fn ($j) => optional($j->jamMengajar)->jam_ke)
            ->values();

        // Jumlah siswa per kelas (tahun ajaran aktif) untuk autofill Jumlah Siswa.
        $siswaPerKelas = SiswaRombel::query()
            ->when($tahunAktif, fn ($q) => $q->where('tahun_ajaran_id', $tahunAktif->id))
            ->selectRaw('rombongan_belajar_id, count(*) as jumlah')
            ->groupBy('rombongan_belajar_id')
            ->pluck('jumlah', 'rombongan_belajar_id');

        // Daftar nama siswa per kelas untuk pilihan "siswa tidak hadir".
        $siswaPerKelasNama = SiswaRombel::with('siswa')
            ->when($tahunAktif, fn ($q) => $q->where('tahun_ajaran_id', $tahunAktif->id))
            ->get()
            ->groupBy('rombongan_belajar_id')
            ->map(fn ($g) => $g->map(fn ($sr) => optional($sr->siswa)->nama_siswa)
                ->filter()->sort()->values());

        return [
            'hariIni' => $hariIni,
            'tahunAktif' => $tahunAktif,
            'guruList' => Guru::where('is_aktif', true)->orderBy('nama_ptk')->get(),
            'kelasList' => Kelas::when($tahunAktif, fn ($q) => $q->where('tahun_ajaran_id', $tahunAktif->id))
                ->orderBy('nama_rombel')->get(),
            'mapelList' => MataPelajaran::orderBy('nama_mapel')->get(),
            'modulList' => ModulAjar::orderByDesc('id')->get(['id', 'judul', 'mata_pelajaran_id']),
            'jadwalHariIni' => $jadwalHariIni,
            'siswaPerKelas' => $siswaPerKelas,
            'siswaPerKelasNama' => $siswaPerKelasNama,
        ];
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'guru_id' => 'required|integer',
            'mengajar_sebagai' => 'required|in:normal,pengganti',
            'jam_ke' => 'nullable|string|max:20',
            'jumlah_jam' => 'required|integer|min:1|max:12',
            'kelas_id' => 'required|integer',
            'paralel' => 'nullable|string|max:10',
            'mata_pelajaran_id' => 'required|integer',
            'jumlah_siswa' => 'required|integer|min:0',
            'hadir' => 'required|integer|min:0',
            'absen' => 'required|integer|min:0',
            'siswa_absen' => 'nullable|string',
            'modul_ajar_id' => 'nullable|integer',
            'materi' => 'nullable|string',
            'catatan' => 'nullable|string',
        ], [], [
            'guru_id' => 'Guru',
            'mengajar_sebagai' => 'Mengajar Sebagai',
            'jam_ke' => 'Jam Ke',
            'jumlah_jam' => 'Jumlah Jam (JP)',
            'kelas_id' => 'Kelas',
            'mata_pelajaran_id' => 'Mata Pelajaran',
            'jumlah_siswa' => 'Jumlah Siswa',
            'modul_ajar_id' => 'Modul Ajar',
            'materi' => 'Materi Pembelajaran',
        ]);
    }

    private function simpanPhoto(Request $request): ?string
    {
        $request->validate(['photo' => 'nullable|image|max:5120']);

        if (! $request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('agenda-mengajar', 'public');
    }
}
