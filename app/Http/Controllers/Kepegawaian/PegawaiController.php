<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\Guru;

class PegawaiController extends BaseCrudController
{
    /**
     * Datacenter tidak punya tabel pegawai umum, hanya tabel guru — jadi
     * menu Data Pegawai kini bersumber dari App\Models\Guru dan hanya
     * mencakup data guru/PTK, bukan staf non-guru (TU, pustakawan, dll).
     */
    protected string $model = Guru::class;
    protected string $routeName = 'kepegawaian.pegawai';
    protected string $title = 'Data Pegawai';

    /**
     * Data bersumber dari database datacenter, jadi menu ini read-only:
     * tidak ada tambah/ubah/hapus dari aplikasi ini.
     */
    protected function canManage(): bool
    {
        return false;
    }

    protected function fields(): array
    {
        return [
            ['name' => 'nip', 'label' => 'NIP', 'type' => 'text'],
            ['name' => 'nama_ptk', 'label' => 'Nama Lengkap', 'type' => 'text'],
            ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
            ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'list' => false],
            ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'list' => false],
            ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea', 'list' => false],
            ['name' => 'nomor_hp', 'label' => 'No. HP', 'type' => 'text', 'list' => false],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email'],
            ['name' => 'jabatan', 'label' => 'Jabatan', 'type' => 'text'],
            ['name' => 'status_kepegawaian', 'label' => 'Status Kepegawaian', 'type' => 'text'],
            ['name' => 'is_aktif', 'label' => 'Aktif', 'type' => 'checkbox'],
            ['name' => 'foto', 'label' => 'Foto', 'type' => 'file', 'list' => false],
        ];
    }
}
