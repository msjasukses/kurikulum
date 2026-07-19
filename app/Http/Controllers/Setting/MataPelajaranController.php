<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\MataPelajaran;

class MataPelajaranController extends BaseCrudController
{
    protected string $model = MataPelajaran::class;
    protected string $routeName = 'setting.mata-pelajaran';
    protected string $title = 'Mata Pelajaran';

    /**
     * Data bersumber dari database datacenter (lihat App\Models\MataPelajaran),
     * jadi menu ini read-only: tidak ada tambah/ubah/hapus dari aplikasi ini.
     */
    protected function canManage(): bool
    {
        return false;
    }

    protected function fields(): array
    {
        return [
            ['name' => 'kode_mapel', 'label' => 'Kode Mapel', 'type' => 'text'],
            ['name' => 'nama_mapel', 'label' => 'Nama Mata Pelajaran', 'type' => 'text'],
            ['name' => 'kelompok', 'label' => 'Kelompok', 'type' => 'text'],
            ['name' => 'tingkat', 'label' => 'Tingkat', 'type' => 'text'],
            ['name' => 'jurusan_id', 'label' => 'Jurusan', 'type' => 'select', 'relation' => ['method' => 'jurusan', 'model' => \App\Models\Jurusan::class, 'display' => 'nama_jurusan']],
            ['name' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea', 'list' => false],
            ['name' => 'is_aktif', 'label' => 'Aktif', 'type' => 'checkbox'],
        ];
    }
}
