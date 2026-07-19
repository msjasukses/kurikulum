<?php

namespace App\Http\Controllers\Kesiswaan;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\OrangTua;

class OrangTuaController extends BaseCrudController
{
    protected string $model = OrangTua::class;
    protected string $routeName = 'kesiswaan.orang-tua';
    protected string $title = 'Data Orang Tua';

    /** Siswa hanya boleh melihat (tanpa tambah/ubah/hapus). */
    protected function canManage(): bool
    {
        return auth()->user() && auth()->user()->isAdmin();
    }

    /** Siswa hanya melihat data orang tuanya sendiri. */
    protected function baseQuery()
    {
        $user = auth()->user();

        return parent::baseQuery()
            ->when($user && $user->isSiswa(), fn ($q) => $q->where('siswa_id', $user->siswa_id ?? 0));
    }

    protected function fields(): array
    {
        return [
            ['name' => 'siswa_id', 'label' => 'Siswa', 'type' => 'select', 'rules' => 'required|integer', 'relation' => ['method' => 'siswa', 'model' => \App\Models\Siswa::class, 'display' => 'nama_siswa']],
            ['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
            ['name' => 'pekerjaan_ayah', 'label' => 'Pekerjaan Ayah', 'type' => 'text', 'rules' => 'nullable|string|max:100', 'list' => false],
            ['name' => 'no_hp_ayah', 'label' => 'No. HP Ayah', 'type' => 'text', 'rules' => 'nullable|string|max:20', 'list' => false],
            ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'type' => 'text', 'rules' => 'nullable|string|max:100'],
            ['name' => 'pekerjaan_ibu', 'label' => 'Pekerjaan Ibu', 'type' => 'text', 'rules' => 'nullable|string|max:100', 'list' => false],
            ['name' => 'no_hp_ibu', 'label' => 'No. HP Ibu', 'type' => 'text', 'rules' => 'nullable|string|max:20', 'list' => false],
            ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea', 'rules' => 'nullable|string', 'list' => false],
            ['name' => 'penghasilan', 'label' => 'Penghasilan Orang Tua', 'type' => 'select', 'rules' => 'nullable|string', 'list' => false, 'options' => ['< 1 juta' => '< Rp 1.000.000', '1-3 juta' => 'Rp 1.000.000 - Rp 3.000.000', '3-5 juta' => 'Rp 3.000.000 - Rp 5.000.000', '> 5 juta' => '> Rp 5.000.000']],
        ];
    }
}
