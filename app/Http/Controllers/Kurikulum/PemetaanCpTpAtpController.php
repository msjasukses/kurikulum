<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Concerns\FilterPenugasanGuru;
use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\IdentitasSekolah;
use App\Models\MataPelajaran;
use App\Models\PemetaanCpTpAtp;
use App\Models\Semester;
use App\Models\TingkatKelas;
use Illuminate\Support\Str;

class PemetaanCpTpAtpController extends BaseCrudController
{
    use FilterPenugasanGuru;

    /** Guru hanya melihat pemetaan untuk mapel & tingkat yang diampunya. */
    protected function baseQuery()
    {
        $mapelIds = $this->mapelIdsGuru();
        $tingkatIds = $this->tingkatIdsGuru();

        return parent::baseQuery()
            ->when($mapelIds !== null, fn ($q) => $q->whereIn('mata_pelajaran_id', $mapelIds))
            ->when($tingkatIds !== null, fn ($q) => $q->whereIn('tingkat_kelas_id', $tingkatIds));
    }

    protected string $model = PemetaanCpTpAtp::class;
    protected string $routeName = 'kurikulum.cp-tp-atp';
    protected string $title = 'Pemetaan CP-TP-ATP';

    /** Pemetaan mengikuti tahun ajaran yang dipilih di topbar. */
    protected ?string $tahunAjaranColumn = 'tahun_ajaran';

    /**
     * Filter daftar: tingkat kelas, mata pelajaran, dan semester. Pilihan
     * tingkat & mapel untuk guru dibatasi sesuai penugasannya.
     */
    protected function filters(): array
    {
        $mapelIds = $this->mapelIdsGuru();
        $tingkatIds = $this->tingkatIdsGuru();

        return [
            [
                'name' => 'tingkat_kelas_id',
                'label' => 'Semua Tingkat Kelas',
                'title' => 'Tingkat Kelas',
                'options' => TingkatKelas::when($tingkatIds !== null, fn ($q) => $q->whereIn('id', $tingkatIds))
                    ->orderBy('urutan')->pluck('nama', 'id')->all(),
            ],
            [
                'name' => 'mata_pelajaran_id',
                'label' => 'Semua Mata Pelajaran',
                'title' => 'Mata Pelajaran',
                'options' => MataPelajaran::when($mapelIds !== null, fn ($q) => $q->whereIn('id', $mapelIds))
                    ->orderBy('nama_mapel')->pluck('nama_mapel', 'id')->all(),
            ],
            [
                'name' => 'semester',
                'label' => 'Semua Semester',
                'title' => 'Semester',
                'options' => Semester::where('is_aktif', 1)->orderBy('urutan')->pluck('nama', 'nama')->all(),
            ],
        ];
    }

    /** Filter yang sedang aktif dalam bentuk ['Mata Pelajaran' => 'Matematika']. */
    private function ringkasanFilter(): array
    {
        $ringkasan = [];

        foreach ($this->filters() as $filter) {
            $nilai = request()->input($filter['name']);

            if ($nilai !== null && $nilai !== '') {
                $judul = $filter['title'] ?? $filter['label'];
                $ringkasan[$judul] = $filter['options'][$nilai] ?? $nilai;
            }
        }

        return $ringkasan;
    }

    protected function extraActions(): array
    {
        // Tombol cetak membawa kata kunci pencarian dan filter yang sedang
        // aktif, supaya hasil cetak sama persis dengan yang tampil di layar.
        $query = $this->filterQuery();

        return [
            ['label' => 'Import Excel', 'url' => route('kurikulum.cp-tp-atp-import.form'), 'icon' => 'bi-upload'],
            ['label' => 'Cetak PDF', 'url' => route('kurikulum.cp-tp-atp-cetak.pdf', $query), 'icon' => 'bi-file-earmark-pdf'],
            ['label' => 'Cetak Word', 'url' => route('kurikulum.cp-tp-atp-cetak.word', $query), 'icon' => 'bi-file-earmark-word'],
        ];
    }

    /** Cetak daftar pemetaan sebagai PDF (dompdf) atau tampilan cetak browser. */
    public function pdf()
    {
        return $this->cetak('pdf');
    }

    /** Cetak daftar pemetaan sebagai dokumen Word (.doc berbasis HTML). */
    public function word()
    {
        return $this->cetak('word');
    }

    private function cetak(string $format)
    {
        $q = trim((string) request('q'));

        $items = $this->applyFilters($this->baseQuery()->with(['mataPelajaran', 'tingkatKelas']))
            ->when($q, function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('capaian_pembelajaran', 'like', "%{$q}%")
                        ->orWhere('tujuan_pembelajaran', 'like', "%{$q}%")
                        ->orWhere('alur_tujuan_pembelajaran', 'like', "%{$q}%")
                        ->orWhere('elemen', 'like', "%{$q}%")
                        ->orWhere('indikator_kktp', 'like', "%{$q}%");
                });
            })
            ->get()
            ->sortBy([
                fn ($a, $b) => strcmp((string) optional($a->mataPelajaran)->nama_mapel, (string) optional($b->mataPelajaran)->nama_mapel),
                fn ($a, $b) => (optional($a->tingkatKelas)->urutan ?? 0) <=> (optional($b->tingkatKelas)->urutan ?? 0),
            ])
            ->values();

        $filterAktif = $this->ringkasanFilter();

        $data = [
            'items' => $items,
            'identitas' => IdentitasSekolah::first(),
            'tahunAjaran' => $this->tahunAjaranTerpilih()->nama(),
            'filterAktif' => $filterAktif,
            'kataKunci' => $q,
            'autoPrint' => false,
        ];

        $view = view('kurikulum.cp-tp-atp-cetak', $data);

        // Nama file ikut menyebutkan filter yang dipakai, mis.
        // "pemetaan-cp-tp-atp-kelas-7-matematika-ganjil.pdf".
        $namaFile = collect(['pemetaan-cp-tp-atp'])
            ->merge(array_values($filterAktif))
            ->push($q)
            ->filter()
            ->map(fn ($bagian) => Str::slug($bagian))
            ->filter()
            ->implode('-');

        if ($format === 'word') {
            return response($view->render(), 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$namaFile.'.doc"',
            ]);
        }

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($view->render())
                ->setPaper('a4', 'landscape')
                ->download($namaFile.'.pdf');
        }

        // Fallback tanpa dompdf: tampilan cetak yang langsung membuka dialog
        // print browser (bisa disimpan sebagai PDF dari sana).
        return view('kurikulum.cp-tp-atp-cetak', ['autoPrint' => true] + $data);
    }

    protected function fields(): array
    {
        return [
            ['name' => 'mata_pelajaran_id', 'label' => 'Mata Pelajaran', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'mataPelajaran', 'model' => \App\Models\MataPelajaran::class, 'display' => 'nama_mapel'] + $this->idsFilter($this->mapelIdsGuru())],
            ['name' => 'tingkat_kelas_id', 'label' => 'Tingkat Kelas', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'tingkatKelas', 'model' => \App\Models\TingkatKelas::class, 'display' => 'nama'] + $this->idsFilter($this->tingkatIdsGuru())],
            ['name' => 'fase', 'label' => 'Fase', 'type' => 'select', 'rules' => 'nullable|string', 'options' => ['A' => 'Fase A', 'B' => 'Fase B', 'C' => 'Fase C', 'D' => 'Fase D', 'E' => 'Fase E', 'F' => 'Fase F']],
            ['name' => 'semester', 'label' => 'Semester', 'type' => 'select', 'rules' => 'nullable|string', 'optionsFrom' => ['model' => \App\Models\Semester::class, 'column' => 'nama', 'orderBy' => 'urutan', 'dir' => 'asc', 'where' => ['is_aktif' => 1]]],
            ['name' => 'elemen', 'label' => 'Elemen', 'type' => 'textarea', 'rules' => 'nullable|string', 'editor' => true],
            ['name' => 'capaian_pembelajaran', 'label' => 'Capaian Pembelajaran (CP)', 'type' => 'textarea', 'rules' => 'required|string', 'editor' => true],
            ['name' => 'tujuan_pembelajaran', 'label' => 'Tujuan Pembelajaran (TP)', 'type' => 'textarea', 'rules' => 'required|string', 'editor' => true],
            ['name' => 'alur_tujuan_pembelajaran', 'label' => 'Alur Tujuan Pembelajaran (ATP)', 'type' => 'textarea', 'rules' => 'required|string', 'editor' => true],
            ['name' => 'indikator_kktp', 'label' => 'Indikator KKTP', 'type' => 'textarea', 'rules' => 'nullable|string', 'editor' => true],
            ['name' => 'model_pembelajaran', 'label' => 'Model Pembelajaran', 'type' => 'checkboxes', 'rules' => 'nullable|array', 'optionsFrom' => ['model' => \App\Models\ModelPembelajaran::class, 'column' => 'nama', 'dir' => 'asc', 'where' => ['is_aktif' => 1]]],
            ['name' => 'sumber_belajar', 'label' => 'Sumber Belajar', 'type' => 'checkboxes', 'rules' => 'nullable|array', 'optionsFrom' => ['model' => \App\Models\SumberBelajar::class, 'column' => 'nama', 'dir' => 'asc', 'where' => ['is_aktif' => 1]]],
            ['name' => 'karakter_dpl', 'label' => 'Karakter 7 KAIH / DPL', 'type' => 'checkboxes', 'rules' => 'nullable|array', 'optionsFrom' => ['model' => \App\Models\KarakterDpl::class, 'column' => 'nama', 'orderBy' => 'urutan', 'dir' => 'asc', 'where' => ['is_aktif' => 1]]],
            ['name' => 'tahun_ajaran', 'label' => 'Tahun Ajaran', 'type' => 'text', 'auto' => true],
        ];
    }
}
