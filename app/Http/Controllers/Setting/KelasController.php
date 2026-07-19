<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\Kelas;

class KelasController extends BaseCrudController
{
    protected string $model = Kelas::class;
    protected string $routeName = 'setting.kelas';
    protected string $title = 'Kelas / Rombel';

    /**
     * Data bersumber dari database datacenter, tabel rombongan_belajar
     * (lihat App\Models\Kelas), jadi menu ini read-only: tidak ada
     * tambah/ubah/hapus dari aplikasi ini.
     */
    protected function canManage(): bool
    {
        return false;
    }

    protected function fields(): array
    {
        return [
            ['name' => 'nama_rombel', 'label' => 'Nama Kelas', 'type' => 'text'],
            ['name' => 'tingkat', 'label' => 'Tingkat', 'type' => 'text'],
            ['name' => 'jurusan_id', 'label' => 'Jurusan', 'type' => 'select', 'relation' => ['method' => 'jurusan', 'model' => \App\Models\Jurusan::class, 'display' => 'nama_jurusan']],
            ['name' => 'tahun_ajaran_id', 'label' => 'Tahun Ajaran', 'type' => 'select', 'relation' => ['method' => 'tahunAjaran', 'model' => \App\Models\DatacenterTahunAjaran::class, 'display' => 'nama_tahun_ajaran']],
            ['name' => 'kapasitas', 'label' => 'Kapasitas', 'type' => 'text'],
        ];
    }
}
