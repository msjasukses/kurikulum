<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Concerns\FilterPenugasanGuru;
use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\IdentitasSekolah;
use App\Models\PemetaanCpTpAtp;
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

    protected function extraActions(): array
    {
        return [
            ['label' => 'Import Excel', 'url' => route('kurikulum.cp-tp-atp-import.form'), 'icon' => 'bi-upload'],
            ['label' => 'Cetak PDF', 'url' => route('kurikulum.cp-tp-atp-cetak.pdf', request()->only('q')), 'icon' => 'bi-file-earmark-pdf'],
            ['label' => 'Cetak Word', 'url' => route('kurikulum.cp-tp-atp-cetak.word', request()->only('q')), 'icon' => 'bi-file-earmark-word'],
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

        $items = $this->baseQuery()->with(['mataPelajaran', 'tingkatKelas'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($x) use ($q) {
                    $x->where('capaian_pembelajaran', 'like', "%{$q}%")
                        ->orWhere('tujuan_pembelajaran', 'like', "%{$q}%")
                        ->orWhere('alur_tujuan_pembelajaran', 'like', "%{$q}%");
                });
            })
            ->get()
            ->sortBy([
                fn ($a, $b) => strcmp((string) optional($a->mataPelajaran)->nama_mapel, (string) optional($b->mataPelajaran)->nama_mapel),
                fn ($a, $b) => (optional($a->tingkatKelas)->urutan ?? 0) <=> (optional($b->tingkatKelas)->urutan ?? 0),
            ])
            ->values();

        $view = view('kurikulum.cp-tp-atp-cetak', [
            'items' => $items,
            'identitas' => IdentitasSekolah::first(),
            'autoPrint' => false,
        ]);

        $namaFile = 'pemetaan-cp-tp-atp'.($q ? '-'.Str::slug($q) : '');

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
        return view('kurikulum.cp-tp-atp-cetak', [
            'items' => $items,
            'identitas' => IdentitasSekolah::first(),
            'autoPrint' => true,
        ]);
    }

    protected function fields(): array
    {
        return [
            ['name' => 'mata_pelajaran_id', 'label' => 'Mata Pelajaran', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'mataPelajaran', 'model' => \App\Models\MataPelajaran::class, 'display' => 'nama_mapel'] + $this->idsFilter($this->mapelIdsGuru())],
            ['name' => 'tingkat_kelas_id', 'label' => 'Tingkat Kelas', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'tingkatKelas', 'model' => \App\Models\TingkatKelas::class, 'display' => 'nama'] + $this->idsFilter($this->tingkatIdsGuru())],
            ['name' => 'fase', 'label' => 'Fase', 'type' => 'select', 'rules' => 'nullable|string', 'options' => ['A' => 'Fase A', 'B' => 'Fase B', 'C' => 'Fase C', 'D' => 'Fase D', 'E' => 'Fase E', 'F' => 'Fase F']],
            ['name' => 'capaian_pembelajaran', 'label' => 'Capaian Pembelajaran (CP)', 'type' => 'textarea', 'rules' => 'required|string'],
            ['name' => 'tujuan_pembelajaran', 'label' => 'Tujuan Pembelajaran (TP)', 'type' => 'textarea', 'rules' => 'required|string'],
            ['name' => 'alur_tujuan_pembelajaran', 'label' => 'Alur Tujuan Pembelajaran (ATP)', 'type' => 'textarea', 'rules' => 'required|string'],
            ['name' => 'tahun_ajaran', 'label' => 'Tahun Ajaran', 'type' => 'select', 'rules' => 'nullable|string|max:20', 'optionsFrom' => ['model' => \App\Models\TahunAjaran::class, 'column' => 'nama_tahun_ajaran']],
        ];
    }
}
