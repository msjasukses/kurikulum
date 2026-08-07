<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\GuruMapel;

class GuruMapelController extends BaseCrudController
{
    protected string $model = GuruMapel::class;
    protected string $routeName = 'kepegawaian.guru-mapel';
    protected string $title = 'Data Guru Mata Pelajaran';

    /** Penugasan mengajar mengikuti tahun ajaran yang dipilih di topbar. */
    protected ?string $tahunAjaranColumn = 'tahun_ajaran_id';

    /**
     * Data bersumber dari database datacenter (lihat App\Models\GuruMapel),
     * jadi menu ini read-only: tidak ada tambah/ubah/hapus dari aplikasi ini.
     */
    protected function canManage(): bool
    {
        return false;
    }

    protected function fields(): array
    {
        return [
            ['name' => 'guru_id', 'label' => 'Guru', 'type' => 'select', 'relation' => ['method' => 'guru', 'model' => \App\Models\Guru::class, 'display' => 'nama_ptk']],
            ['name' => 'mata_pelajaran_id', 'label' => 'Mata Pelajaran', 'type' => 'select', 'relation' => ['method' => 'mataPelajaran', 'model' => \App\Models\MataPelajaran::class, 'display' => 'nama_mapel']],
            ['name' => 'rombongan_belajar_id', 'label' => 'Kelas', 'type' => 'select', 'relation' => ['method' => 'kelas', 'model' => \App\Models\Kelas::class, 'display' => 'nama_rombel']],
            ['name' => 'tahun_ajaran_id', 'label' => 'Tahun Ajaran', 'type' => 'select', 'relation' => ['method' => 'tahunAjaran', 'model' => \App\Models\TahunAjaran::class, 'display' => 'nama_tahun_ajaran']],
        ];
    }
}
