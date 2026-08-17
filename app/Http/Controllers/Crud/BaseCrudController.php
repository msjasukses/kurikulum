<?php

namespace App\Http\Controllers\Crud;

use App\Http\Controllers\Controller;
use App\Support\TahunAjaranTerpilih;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

abstract class BaseCrudController extends Controller
{
    /** @var class-string Nama kelas Model Eloquent */
    protected string $model;

    /** Nama route prefix, mis. "kepegawaian.pegawai" */
    protected string $routeName;

    /** Judul halaman, mis. "Data Pegawai" */
    protected string $title;

    /** Kolom default untuk urutan data */
    protected string $orderBy = 'id';

    protected string $orderDir = 'desc';

    protected int $perPage = 10;

    /**
     * Kolom penanda tahun ajaran pada model ini. Bila diisi, daftar dan
     * penyimpanan data otomatis mengikuti tahun ajaran yang dipilih di
     * dropdown topbar. Dua bentuk yang dipakai di aplikasi ini:
     *  - 'tahun_ajaran_id' : relasi id ke tabel tahun_ajaran
     *  - 'tahun_ajaran'    : teks nama tahun ajaran, mis. "2026/2027"
     */
    protected ?string $tahunAjaranColumn = null;

    protected function tahunAjaranTerpilih(): TahunAjaranTerpilih
    {
        return app(TahunAjaranTerpilih::class);
    }

    /** Nilai kolom tahun ajaran sesuai pilihan topbar (id atau nama). */
    protected function nilaiTahunAjaran(): int|string|null
    {
        if (! $this->tahunAjaranColumn) {
            return null;
        }

        return $this->tahunAjaranColumn === 'tahun_ajaran_id'
            ? $this->tahunAjaranTerpilih()->id()
            : $this->tahunAjaranTerpilih()->nama();
    }

    /** Atribut tahun ajaran yang ikut disimpan saat tambah/ubah data. */
    protected function tahunAjaranAttribute(): array
    {
        $nilai = $this->nilaiTahunAjaran();

        return $nilai === null ? [] : [$this->tahunAjaranColumn => $nilai];
    }

    /**
     * Apakah user saat ini boleh tambah/ubah/hapus data (bukan hanya lihat).
     * Override di controller turunan bila perlu dibatasi (mis. siswa read-only).
     */
    protected function canManage(): bool
    {
        return true;
    }

    /**
     * Query dasar. Override untuk menambah scope, mis. where('role','admin').
     */
    protected function baseQuery()
    {
        $query = ($this->model)::query();
        $nilai = $this->nilaiTahunAjaran();

        if ($nilai !== null) {
            $kolom = $this->tahunAjaranColumn;

            // Untuk kolom teks, data lama yang belum diberi tahun ajaran
            // (mis. hasil import) tetap ditampilkan supaya tidak "hilang".
            $query->where(fn ($q) => $kolom === 'tahun_ajaran_id'
                ? $q->where($kolom, $nilai)
                : $q->where($kolom, $nilai)->orWhereNull($kolom));
        }

        return $query;
    }

    /**
     * Atribut tambahan yang selalu di-set saat create/update (mis. role tetap).
     */
    protected function defaultAttributes(): array
    {
        return [];
    }

    /**
     * Definisi field untuk form & tabel.
     */
    abstract protected function fields(): array;

    /**
     * Tombol aksi tambahan di halaman index, di samping tombol Tambah.
     * Override di controller turunan bila perlu, mis. tombol Import.
     * Setiap item: ['label' => string, 'url' => string, 'icon' => string|null].
     */
    protected function extraActions(): array
    {
        return [];
    }

    /**
     * Dropdown filter di halaman index, di samping kotak pencarian.
     * Setiap item: [
     *   'name'    => nama parameter query (default juga jadi nama kolom),
     *   'label'   => teks pilihan kosong, mis. "Semua Mata Pelajaran",
     *   'options' => [nilai => label],
     *   'column'  => (opsional) nama kolom bila berbeda dari 'name',
     * ].
     */
    protected function filters(): array
    {
        return [];
    }

    /** Terapkan filter yang sedang aktif (dari query string) ke query. */
    protected function applyFilters($query)
    {
        foreach ($this->filters() as $filter) {
            $nilai = request()->input($filter['name']);

            if ($nilai !== null && $nilai !== '') {
                $query->where($filter['column'] ?? $filter['name'], $nilai);
            }
        }

        return $query;
    }

    /** Parameter yang perlu ikut dibawa ke link lain, mis. tombol cetak. */
    protected function filterQuery(): array
    {
        return request()->only(array_merge(['q'], array_column($this->filters(), 'name')));
    }

    protected function relationsToLoad(): array
    {
        return collect($this->fields())
            ->filter(fn ($f) => isset($f['relation']))
            ->map(fn ($f) => $f['relation']['method'])
            ->values()
            ->all();
    }

    protected function uploadFolder(): string
    {
        return 'uploads/'.Str::snake(class_basename($this->model));
    }

    public function index(Request $request): View
    {
        $query = $this->baseQuery();

        if (! empty($this->relationsToLoad())) {
            $query->with($this->relationsToLoad());
        }

        $this->applyFilters($query);

        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $searchable = collect($this->fields())
                ->filter(fn ($f) => in_array($f['type'], ['text', 'textarea', 'email']) && ! isset($f['relation']))
                ->pluck('name');

            $query->where(function ($sub) use ($searchable, $keyword) {
                foreach ($searchable as $column) {
                    $sub->orWhere($column, 'like', '%'.$keyword.'%');
                }
            });
        }

        $items = $query->orderBy($this->orderBy, $this->orderDir)->paginate($this->perPage)->withQueryString();

        return view('crud.index', [
            'items' => $items,
            'fields' => $this->fields(),
            'title' => $this->title,
            'routeName' => $this->routeName,
            'q' => $request->input('q'),
            'canManage' => $this->canManage(),
            'extraActions' => $this->extraActions(),
            'filters' => $this->filters(),
        ]);
    }

    public function create(): View
    {
        abort_unless($this->canManage(), 403);

        return view('crud.form', [
            'fields' => $this->resolveFieldOptions($this->fields()),
            'title' => $this->title,
            'routeName' => $this->routeName,
            'item' => null,
            'canManage' => $this->canManage(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->canManage(), 403);

        $fields = $this->fields();
        $validated = $request->validate($this->buildValidationRules($fields, false));

        $data = array_merge(
            $this->extractData($request, $validated, $fields, null),
            $this->tahunAjaranAttribute(),
            $this->defaultAttributes()
        );

        ($this->model)::create($data);

        return redirect()->route($this->routeName.'.index')
            ->with('success', $this->title.' berhasil ditambahkan.');
    }

    public function edit(int|string $id): View
    {
        abort_unless($this->canManage(), 403);

        $item = $this->baseQuery()->findOrFail($id);

        return view('crud.form', [
            'fields' => $this->resolveFieldOptions($this->fields(), $item),
            'title' => $this->title,
            'routeName' => $this->routeName,
            'item' => $item,
            'canManage' => $this->canManage(),
        ]);
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        abort_unless($this->canManage(), 403);

        $item = $this->baseQuery()->findOrFail($id);
        $fields = $this->fields();
        $validated = $request->validate($this->buildValidationRules($fields, true, $id));

        $data = array_merge(
            $this->extractData($request, $validated, $fields, $item),
            $this->tahunAjaranAttribute(),
            $this->defaultAttributes()
        );

        $item->update($data);

        return redirect()->route($this->routeName.'.index')
            ->with('success', $this->title.' berhasil diperbarui.');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        abort_unless($this->canManage(), 403);

        $item = $this->baseQuery()->findOrFail($id);

        foreach ($this->fields() as $field) {
            if ($field['type'] === 'file' && ! empty($item->{$field['name']})) {
                Storage::disk('public')->delete($item->{$field['name']});
            }
        }

        $item->delete();

        return redirect()->route($this->routeName.'.index')
            ->with('success', $this->title.' berhasil dihapus.');
    }

    /**
     * Filter tahun ajaran untuk relasi yang punya kolom 'tahun_ajaran_id',
     * mis. pilihan Kelas/Rombel. Dipakai seperti idsFilter():
     * 'relation' => [...] + $this->filterTahunAjaranId().
     */
    protected function filterTahunAjaranId(): array
    {
        $id = $this->tahunAjaranTerpilih()->id();

        return $id ? ['where' => ['tahun_ajaran_id' => $id]] : [];
    }

    protected function resolveFieldOptions(array $fields, $item = null): array
    {
        foreach ($fields as &$field) {
            // Field yang nilainya diisi otomatis oleh sistem — untuk saat ini
            // hanya kolom tahun ajaran, yang mengikuti pilihan di topbar.
            if (! empty($field['auto']) && $field['name'] === $this->tahunAjaranColumn) {
                $field['autoValue'] = $this->tahunAjaranTerpilih()->nama();
            }

            if ($field['type'] === 'select' && isset($field['relation'])) {
                $relModel = $field['relation']['model'];
                $display = $field['relation']['display'];
                $orderBy = $field['relation']['orderBy'] ?? $display;
                // get()->pluck() (bukan pluck() langsung di query builder) supaya
                // $display boleh berupa accessor (mis. atribut "label" hasil
                // Attribute::make()), bukan hanya kolom asli di tabel.
                // 'ids' (opsional) membatasi pilihan ke id tertentu, mis.
                // mapel/kelas sesuai penugasan guru yang sedang login.
                // 'where' (opsional) menambah kondisi, mis. kelas hanya untuk
                // tahun ajaran yang dipilih di topbar.
                $field['options'] = ($relModel)::orderBy($orderBy)
                    ->when(isset($field['relation']['ids']), fn ($q) => $q->whereIn('id', $field['relation']['ids']))
                    ->when(! empty($field['relation']['where']), fn ($q) => $q->where($field['relation']['where']))
                    ->get()->pluck($display, 'id')->all();

                // Nilai yang sudah tersimpan tetap muncul walau di luar filter
                // (mis. mengubah data lama dari tahun ajaran sebelumnya),
                // supaya tidak ikut terhapus saat data disimpan ulang.
                $tersimpan = $item?->{$field['name']};
                if ($tersimpan && ! array_key_exists($tersimpan, $field['options'])) {
                    $lama = ($relModel)::find($tersimpan);
                    if ($lama) {
                        $field['options'][$tersimpan] = $lama->{$display};
                    }
                }
            }

            // Untuk field yang menyimpan teks langsung (bukan id relasi), tapi pilihannya
            // diambil dinamis dari tabel master lain, mis. kolom tahun_ajaran (string)
            // atau daftar checkbox Model Pembelajaran / Sumber Belajar.
            // Kunci opsional: 'orderBy' (default = kolom nilai), 'dir' (default 'desc'),
            // dan 'where' (array kondisi, mis. ['is_aktif' => 1]).
            if (in_array($field['type'], ['select', 'checkboxes'], true) && isset($field['optionsFrom'])) {
                $srcModel = $field['optionsFrom']['model'];
                $column = $field['optionsFrom']['column'];
                $field['options'] = ($srcModel)::query()
                    ->when(isset($field['optionsFrom']['where']), fn ($q) => $q->where($field['optionsFrom']['where']))
                    ->orderBy($field['optionsFrom']['orderBy'] ?? $column, $field['optionsFrom']['dir'] ?? 'desc')
                    ->pluck($column, $column)->all();
            }
        }

        return $fields;
    }

    protected function buildValidationRules(array $fields, bool $isUpdate, int|string|null $id = null): array
    {
        $rules = [];

        foreach ($fields as $field) {
            if (! isset($field['rules'])) {
                continue;
            }

            $fieldRules = is_array($field['rules']) ? $field['rules'] : explode('|', $field['rules']);
            $final = [];

            foreach ($fieldRules as $rule) {
                if (Str::startsWith($rule, 'unique:')) {
                    $params = explode(',', Str::after($rule, 'unique:'));
                    $table = $params[0];
                    $column = $params[1] ?? $field['name'];
                    $uniqueRule = Rule::unique($table, $column);
                    if ($isUpdate && $id) {
                        $uniqueRule = $uniqueRule->ignore($id);
                    }
                    $final[] = $uniqueRule;
                } else {
                    $final[] = $rule;
                }
            }

            if ($field['type'] === 'file' && $isUpdate) {
                $final = array_values(array_filter($final, fn ($r) => $r !== 'required'));
                array_unshift($final, 'nullable');
            }

            if ($field['type'] === 'password' && $isUpdate) {
                $final = array_values(array_filter($final, fn ($r) => $r !== 'required'));
                array_unshift($final, 'nullable');
            }

            $rules[$field['name']] = $final;

            // Field pilihan ganda dikirim sebagai array, jadi tiap isinya
            // divalidasi terpisah.
            if ($field['type'] === 'checkboxes') {
                $rules[$field['name'].'.*'] = $field['itemRules'] ?? 'string|max:255';
            }
        }

        return $rules;
    }

    protected function extractData(Request $request, array $validated, array $fields, $existing = null): array
    {
        $data = [];

        foreach ($fields as $field) {
            $name = $field['name'];

            if ($field['type'] === 'file') {
                if ($request->hasFile($name)) {
                    if ($existing && ! empty($existing->{$name})) {
                        Storage::disk('public')->delete($existing->{$name});
                    }
                    $data[$name] = $request->file($name)->store($this->uploadFolder(), 'public');
                }
                continue;
            }

            if ($field['type'] === 'password') {
                if ($request->filled($name)) {
                    $data[$name] = bcrypt($validated[$name]);
                }
                continue;
            }

            if ($field['type'] === 'checkbox') {
                $data[$name] = $request->boolean($name);
                continue;
            }

            // Textarea dengan editor teks kaya: HTML-nya disaring dulu.
            if (! empty($field['editor'])) {
                $data[$name] = \App\Support\TeksKaya::bersihkan($validated[$name] ?? null);
                continue;
            }

            // Pilihan ganda: bila tidak ada satu pun yang dicentang, browser
            // tidak mengirim key-nya sama sekali sehingga isinya jadi array kosong.
            if ($field['type'] === 'checkboxes') {
                $dipilih = array_values(array_filter(
                    (array) ($validated[$name] ?? []),
                    fn ($v) => $v !== null && $v !== ''
                ));
                $data[$name] = $dipilih;
                continue;
            }

            $data[$name] = $validated[$name] ?? null;
        }

        return $data;
    }
}
