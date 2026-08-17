<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\ModelPembelajaran;

class ModelPembelajaranController extends BaseCrudController
{
    protected string $model = ModelPembelajaran::class;
    protected string $routeName = 'setting.model-pembelajaran';
    protected string $title = 'Model Pembelajaran';

    protected string $orderBy = 'nama';
    protected string $orderDir = 'asc';

    protected function fields(): array
    {
        return [
            ['name' => 'nama', 'label' => 'Nama Model', 'type' => 'text', 'rules' => 'required|string|max:150|unique:model_pembelajaran,nama', 'placeholder' => 'Problem Based Learning (PBL)'],
            ['name' => 'sintaks', 'label' => 'Sintaks / Tahapan', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
            ['name' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => 'nullable|string', 'list' => false],
            ['name' => 'is_aktif', 'label' => 'Aktif', 'type' => 'checkbox'],
        ];
    }
}
