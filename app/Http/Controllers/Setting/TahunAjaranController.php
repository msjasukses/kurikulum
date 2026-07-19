<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\TahunAjaran;

class TahunAjaranController extends BaseCrudController
{
    protected string $model = TahunAjaran::class;
    protected string $routeName = 'setting.tahun-ajaran';
    protected string $title = 'Tahun Ajaran';

    protected string $orderBy = 'nama_tahun_ajaran';
    protected string $orderDir = 'desc';

    /**
     * Data bersumber dari database datacenter (lihat App\Models\TahunAjaran),
     * jadi menu ini read-only: tidak ada tambah/ubah/hapus dari aplikasi ini.
     */
    protected function canManage(): bool
    {
        return false;
    }

    protected function fields(): array
    {
        return [
            ['name' => 'kode_tahun_ajaran', 'label' => 'Kode', 'type' => 'text'],
            ['name' => 'nama_tahun_ajaran', 'label' => 'Tahun Ajaran', 'type' => 'text'],
            ['name' => 'tanggal_mulai', 'label' => 'Tanggal Mulai', 'type' => 'date'],
            ['name' => 'tanggal_selesai', 'label' => 'Tanggal Selesai', 'type' => 'date'],
            ['name' => 'is_aktif', 'label' => 'Aktif', 'type' => 'checkbox'],
        ];
    }
}
