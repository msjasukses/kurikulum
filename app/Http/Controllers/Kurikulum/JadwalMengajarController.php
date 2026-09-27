<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Concerns\FilterPenugasanGuru;
use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\JadwalMengajar;
use App\Models\JamMengajar;
use App\Models\Kelas;
use App\Models\TingkatKelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JadwalMengajarController extends BaseCrudController
{
    use FilterPenugasanGuru;

    /** Guru hanya melihat jadwal mengajar miliknya sendiri. */
    protected function baseQuery()
    {
        $user = auth()->user();

        return parent::baseQuery()
            ->when($user && $user->isGuru() && $user->guru_id, fn ($q) => $q->where('guru_id', $user->guru_id));
    }

    protected string $model = JadwalMengajar::class;
    protected string $routeName = 'kurikulum.jadwal';
    protected string $title = 'Jadwal Mengajar Guru';

    /** Jadwal mengikuti tahun ajaran yang dipilih di topbar. */
    protected ?string $tahunAjaranColumn = 'tahun_ajaran';

    /** Pilihan Jam Mengajar (checkbox) disaring mengikuti Hari & Kelas. */
    protected function formScript(): ?string
    {
        return 'kurikulum.jadwal-form-script';
    }

    protected function formData(): array
    {
        // Kelas/rombel menyimpan nomor tingkat (kolom "tingkat"), sedangkan
        // Jam Mengajar menyimpan id tingkat_kelas — jadi dipetakan dulu.
        $tingkatPerNomor = TingkatKelas::pluck('id', 'nomor');

        return [
            'jamMengajarList' => JamMengajar::orderBy('jam_ke')->get()
                ->map(fn ($j) => [
                    'id' => $j->id,
                    'label' => $j->label,
                    'hari' => $j->hari,
                    'tingkat_kelas_id' => $j->tingkat_kelas_id,
                ])->values(),
            'tingkatPerKelas' => Kelas::get(['id', 'tingkat'])
                ->mapWithKeys(fn ($k) => [$k->id => $tingkatPerNomor[$k->tingkat] ?? null]),
        ];
    }
    /**
     * Satu jadwal disimpan per jam pelajaran. Karena pilihan jamnya berupa
     * checkbox, satu kali simpan bisa menghasilkan beberapa baris jadwal —
     * satu untuk tiap jam yang dicentang.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->canManage(), 403);

        $fields = $this->fields();
        $validated = $request->validate($this->buildValidationRules($fields, false));

        $jumlah = $this->simpanPerJam(
            $this->dataDasar($request, $validated, $fields),
            $validated['jam_mengajar_id'] ?? []
        );

        return redirect()->route($this->routeName.'.index')
            ->with('success', $this->title.' berhasil ditambahkan ('.$jumlah.' jam).');
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        abort_unless($this->canManage(), 403);

        $item = $this->baseQuery()->findOrFail($id);
        $fields = $this->fields();
        $validated = $request->validate($this->buildValidationRules($fields, true, $id));

        $jam = array_values(array_filter((array) ($validated['jam_mengajar_id'] ?? [])));
        $dasar = $this->dataDasar($request, $validated, $fields, $item);

        // Baris yang sedang diubah memakai jam pertama; jam lain yang ikut
        // dicentang disimpan sebagai baris jadwal tambahan.
        $item->update($dasar + ['jam_mengajar_id' => array_shift($jam)]);
        $jumlah = 1 + $this->simpanPerJam($dasar, $jam);

        return redirect()->route($this->routeName.'.index')
            ->with('success', $this->title.' berhasil diperbarui ('.$jumlah.' jam).');
    }

    /** Atribut jadwal selain jam mengajar. */
    private function dataDasar(Request $request, array $validated, array $fields, $existing = null): array
    {
        $data = array_merge(
            $this->extractData($request, $validated, $fields, $existing),
            $this->tahunAjaranAttribute(),
            $this->defaultAttributes()
        );

        unset($data['jam_mengajar_id']);

        return $data;
    }

    /**
     * Simpan satu baris jadwal per jam. Kombinasi guru + kelas + hari + jam
     * + tahun ajaran yang sudah ada diperbarui, bukan digandakan.
     */
    private function simpanPerJam(array $dasar, array $jamIds): int
    {
        $jumlah = 0;

        foreach (array_unique(array_filter($jamIds)) as $jamId) {
            JadwalMengajar::updateOrCreate(
                [
                    'guru_id' => $dasar['guru_id'] ?? null,
                    'kelas_id' => $dasar['kelas_id'] ?? null,
                    'hari' => $dasar['hari'] ?? null,
                    'jam_mengajar_id' => $jamId,
                    'tahun_ajaran' => $dasar['tahun_ajaran'] ?? null,
                ],
                $dasar
            );
            $jumlah++;
        }

        return $jumlah;
    }

    protected function fields(): array
    {
        return [
            ['name' => 'hari', 'label' => 'Hari', 'type' => 'select', 'rules' => 'required|string', 'options' => ['Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu', 'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu']],
            ['name' => 'guru_id', 'label' => 'Guru', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'guru', 'model' => \App\Models\Guru::class, 'display' => 'nama_ptk'] + $this->idsFilter(auth()->user()?->isGuru() && auth()->user()->guru_id ? [auth()->user()->guru_id] : null)],
            ['name' => 'mata_pelajaran_id', 'label' => 'Mata Pelajaran', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'mataPelajaran', 'model' => \App\Models\MataPelajaran::class, 'display' => 'nama_mapel'] + $this->idsFilter($this->mapelIdsGuru())],
            ['name' => 'kelas_id', 'label' => 'Kelas', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'kelas', 'model' => \App\Models\Kelas::class, 'display' => 'nama_rombel'] + $this->idsFilter($this->kelasIdsGuru()) + $this->filterTahunAjaranId()],
            ['name' => 'jam_mengajar_id', 'label' => 'Jam Mengajar', 'type' => 'select', 'multiple' => true, 'rules' => 'required|array|min:1', 'itemRules' => 'integer', 'hint' => 'Centang semua jam pelajaran yang diampu. Tiap jam disimpan sebagai satu baris jadwal.', 'relation' => ['method' => 'jamMengajar', 'model' => \App\Models\JamMengajar::class, 'display' => 'label', 'orderBy' => 'jam_ke']],
            ['name' => 'ruangan', 'label' => 'Ruangan', 'type' => 'text', 'rules' => 'nullable|string|max:50', 'list' => false],
            ['name' => 'tahun_ajaran', 'label' => 'Tahun Ajaran', 'type' => 'text', 'auto' => true],
        ];
    }
}
