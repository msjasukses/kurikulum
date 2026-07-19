<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\User;

class UserGuruController extends BaseCrudController
{
    protected string $model = User::class;
    protected string $routeName = 'users.guru';
    protected string $title = 'User Guru';

    protected function baseQuery()
    {
        return User::query()->where('role', User::ROLE_GURU);
    }

    protected function defaultAttributes(): array
    {
        return ['role' => User::ROLE_GURU];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'rules' => 'required|string|max:100'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email|max:100|unique:users,email'],
            ['name' => 'password', 'label' => 'Kata Sandi', 'type' => 'password', 'rules' => 'required|string|min:6'],
            ['name' => 'pegawai_id', 'label' => 'Data Pegawai (Guru)', 'type' => 'select', 'rules' => 'nullable|integer', 'relation' => ['method' => 'pegawai', 'model' => \App\Models\Pegawai::class, 'display' => 'nama']],
            ['name' => 'aktif', 'label' => 'Aktif', 'type' => 'checkbox'],
        ];
    }
}
