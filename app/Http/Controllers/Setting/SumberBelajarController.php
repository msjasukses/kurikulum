<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\SumberBelajar;

class SumberBelajarController extends BaseCrudController
{
    protected string $model = SumberBelajar::class;
    protected string $routeName = 'setting.sumber-belajar';
    protected string $title = 'Sumber Belajar';

    protected string $orderBy = 'nama';
    protected string $orderDir = 'asc';

    protected function fields(): array
    {
        return [
            ['name' => 'nama', 'label' => 'Nama Sumber Belajar', 'type' => 'text', 'rules' => 'required|string|max:150|unique:sumber_belajar,nama', 'placeholder' => 'Buku Siswa'],
            ['name' => 'jenis', 'label' => 'Jenis', 'type' => 'select', 'rules' => 'nullable|string', 'options' => ['Cetak' => 'Cetak', 'Digital' => 'Digital', 'Lingkungan' => 'Lingkungan', 'Lainnya' => 'Lainnya']],
            ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'rules' => 'nullable|string', 'list' => false],
            ['name' => 'is_aktif', 'label' => 'Aktif', 'type' => 'checkbox'],
        ];
    }
}
