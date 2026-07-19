<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\User;

class UserSiswaController extends BaseCrudController
{
    protected string $model = User::class;
    protected string $routeName = 'users.siswa';
    protected string $title = 'User Siswa';

    protected function baseQuery()
    {
        return User::query()->where('role', User::ROLE_SISWA);
    }

    protected function defaultAttributes(): array
    {
        return ['role' => User::ROLE_SISWA];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'rules' => 'required|string|max:100'],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email|max:100|unique:users,email'],
            ['name' => 'password', 'label' => 'Kata Sandi', 'type' => 'password', 'rules' => 'required|string|min:6'],
            ['name' => 'siswa_id', 'label' => 'Data Siswa', 'type' => 'select', 'rules' => 'nullable|integer', 'relation' => ['method' => 'siswa', 'model' => \App\Models\Siswa::class, 'display' => 'nama_siswa']],
            ['name' => 'aktif', 'label' => 'Aktif', 'type' => 'checkbox'],
        ];
    }
}
