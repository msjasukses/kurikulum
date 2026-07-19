<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\TingkatKelas;

class TingkatKelasController extends BaseCrudController
{
    protected string $model = TingkatKelas::class;
    protected string $routeName = 'setting.tingkat-kelas';
    protected string $title = 'Tingkat Kelas';

    /**
     * Data bersumber dari database datacenter (lihat App\Models\TingkatKelas),
     * jadi menu ini read-only: tidak ada tambah/ubah/hapus dari aplikasi ini.
     */
    protected function canManage(): bool
    {
        return false;
    }

    protected function fields(): array
    {
        return [
            ['name' => 'kode', 'label' => 'Kode', 'type' => 'text'],
            ['name' => 'nama', 'label' => 'Nama Tingkat', 'type' => 'text'],
            ['name' => 'nomor', 'label' => 'Nomor', 'type' => 'text'],
            ['name' => 'jenjang', 'label' => 'Jenjang', 'type' => 'text'],
            ['name' => 'urutan', 'label' => 'Urutan', 'type' => 'text'],
            ['name' => 'is_aktif', 'label' => 'Aktif', 'type' => 'checkbox'],
        ];
    }
}
