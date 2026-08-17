<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\KarakterDpl;

class KarakterDplController extends BaseCrudController
{
    protected string $model = KarakterDpl::class;
    protected string $routeName = 'setting.karakter-dpl';
    protected string $title = 'Karakter 7 KAIH / DPL';

    protected string $orderBy = 'urutan';
    protected string $orderDir = 'asc';

    protected int $perPage = 20;

    protected function fields(): array
    {
        return [
            ['name' => 'nama', 'label' => 'Nama Karakter', 'type' => 'text', 'rules' => 'required|string|max:150|unique:karakter_dpl,nama', 'placeholder' => 'Bangun Pagi'],
            ['name' => 'kategori', 'label' => 'Kategori', 'type' => 'select', 'rules' => 'nullable|string', 'options' => ['7 KAIH' => '7 KAIH (7 Kebiasaan Anak Indonesia Hebat)', 'DPL' => 'DPL (Dimensi Profil Lulusan)']],
            ['name' => 'urutan', 'label' => 'Urutan', 'type' => 'number', 'rules' => 'nullable|integer|min:0'],
            ['name' => 'deskripsi', 'label' => 'Deskripsi', 'type' => 'textarea', 'rules' => 'nullable|string', 'list' => false],
            ['name' => 'is_aktif', 'label' => 'Aktif', 'type' => 'checkbox'],
        ];
    }
}
