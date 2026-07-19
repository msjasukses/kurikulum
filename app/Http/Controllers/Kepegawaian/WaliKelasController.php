<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\WaliKelas;

class WaliKelasController extends BaseCrudController
{
    protected string $model = WaliKelas::class;
    protected string $routeName = 'kepegawaian.wali-kelas';
    protected string $title = 'Data Wali Kelas';

    /**
     * Data bersumber dari database datacenter, jadi menu ini read-only:
     * tidak ada tambah/ubah/hapus dari aplikasi ini.
     */
    protected function canManage(): bool
    {
        return false;
    }

    /**
     * Datacenter tidak punya tabel wali_kelas tersendiri — data ini
     * diturunkan dari rombongan_belajar, hanya baris yang sudah punya
     * wali kelas (wali_kelas_id terisi) yang ditampilkan.
     */
    protected function baseQuery()
    {
        return parent::baseQuery()->whereNotNull('wali_kelas_id');
    }

    protected function fields(): array
    {
        return [
            ['name' => 'nama_rombel', 'label' => 'Kelas', 'type' => 'text'],
            ['name' => 'wali_kelas_id', 'label' => 'Wali Kelas', 'type' => 'select', 'relation' => ['method' => 'guru', 'model' => \App\Models\Guru::class, 'display' => 'nama_ptk']],
            ['name' => 'tahun_ajaran_id', 'label' => 'Tahun Ajaran', 'type' => 'select', 'relation' => ['method' => 'tahunAjaran', 'model' => \App\Models\TahunAjaran::class, 'display' => 'nama_tahun_ajaran']],
        ];
    }
}
