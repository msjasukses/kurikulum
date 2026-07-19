<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\JenisEskul;

class JenisEskulController extends BaseCrudController
{
    protected string $model = JenisEskul::class;
    protected string $routeName = 'setting.jenis-eskul';
    protected string $title = 'Jenis Eskul';

    protected function fields(): array
    {
        return [
            ['name' => 'nama_eskul', 'label' => 'Nama Eskul', 'type' => 'text', 'rules' => 'required|string|max:100|unique:jenis_eskul,nama_eskul'],
            ['name' => 'pembina', 'label' => 'Pembina', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
            ['name' => 'hari', 'label' => 'Hari', 'type' => 'select', 'rules' => 'nullable|string', 'options' => ['Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu', 'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu', 'Minggu' => 'Minggu']],
            ['name' => 'jam', 'label' => 'Jam', 'type' => 'text', 'rules' => 'nullable|string|max:50', 'placeholder' => '15:00 - 17:00'],
            ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'textarea', 'rules' => 'nullable|string'],
        ];
    }
}
