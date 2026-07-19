<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\Jurusan;

class JurusanController extends BaseCrudController
{
    protected string $model = Jurusan::class;
    protected string $routeName = 'setting.jurusan';
    protected string $title = 'Jurusan';

    /**
     * Data bersumber dari database datacenter (lihat App\Models\Jurusan),
     * jadi menu ini read-only: tidak ada tambah/ubah/hapus dari aplikasi ini.
     */
    protected function canManage(): bool
    {
        return false;
    }

    protected function fields(): array
    {
        return [
            ['name' => 'kode_jurusan', 'label' => 'Kode Jurusan', 'type' => 'text'],
            ['name' => 'nama_jurusan', 'label' => 'Nama Jurusan', 'type' => 'text'],
            ['name' => 'singkatan', 'label' => 'Singkatan', 'type' => 'text'],
            ['name' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea', 'list' => false],
            ['name' => 'is_aktif', 'label' => 'Aktif', 'type' => 'checkbox'],
        ];
    }
}
