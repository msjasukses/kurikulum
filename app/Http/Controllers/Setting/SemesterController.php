<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\Semester;

class SemesterController extends BaseCrudController
{
    protected string $model = Semester::class;
    protected string $routeName = 'setting.semester';
    protected string $title = 'Semester';

    protected string $orderBy = 'urutan';
    protected string $orderDir = 'asc';

    protected function fields(): array
    {
        return [
            ['name' => 'kode', 'label' => 'Kode', 'type' => 'text', 'rules' => 'nullable|string|max:10', 'placeholder' => '1'],
            ['name' => 'nama', 'label' => 'Nama Semester', 'type' => 'text', 'rules' => 'required|string|max:50|unique:semester,nama', 'placeholder' => 'Ganjil'],
            ['name' => 'urutan', 'label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
            ['name' => 'is_aktif', 'label' => 'Aktif', 'type' => 'checkbox'],
        ];
    }
}
