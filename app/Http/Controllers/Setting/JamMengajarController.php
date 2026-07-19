<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\JamMengajar;

class JamMengajarController extends BaseCrudController
{
    protected string $model = JamMengajar::class;
    protected string $routeName = 'setting.jam-mengajar';
    protected string $title = 'Jam Mengajar';

    protected function fields(): array
    {
        return [
            ['name' => 'jam_ke', 'label' => 'Jam Ke-', 'type' => 'number', 'rules' => 'required|integer|min:1'],
            ['name' => 'tingkat_kelas_id', 'label' => 'Tingkat Kelas', 'type' => 'select', 'rules' => 'nullable|integer', 'relation' => ['method' => 'tingkatKelas', 'model' => \App\Models\TingkatKelas::class, 'display' => 'nama']],
            ['name' => 'hari', 'label' => 'Hari', 'type' => 'select', 'rules' => 'nullable|string', 'options' => ['Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu', 'Kamis' => 'Kamis', 'Jumat' => 'Jumat', 'Sabtu' => 'Sabtu']],
            ['name' => 'nama', 'label' => 'Nama', 'type' => 'text', 'rules' => 'nullable|string|max:50', 'placeholder' => 'Contoh: Jam ke-1'],
            ['name' => 'jam_mulai', 'label' => 'Jam Mulai', 'type' => 'time', 'rules' => 'required'],
            ['name' => 'jam_selesai', 'label' => 'Jam Selesai', 'type' => 'time', 'rules' => 'required'],
            ['name' => 'keterangan', 'label' => 'Keterangan', 'type' => 'text', 'rules' => 'nullable|string|max:100', 'placeholder' => 'Contoh: Istirahat'],
        ];
    }
}
