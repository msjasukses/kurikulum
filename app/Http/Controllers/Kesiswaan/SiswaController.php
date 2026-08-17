<?php

namespace App\Http\Controllers\Kesiswaan;

use App\Http\Controllers\Crud\BaseCrudController;
use App\Models\Siswa;

class SiswaController extends BaseCrudController
{
    protected string $model = Siswa::class;
    protected string $routeName = 'kesiswaan.siswa';
    protected string $title = 'Data Siswa';

    /**
     * Data bersumber dari database datacenter (lihat App\Models\Siswa),
     * jadi menu ini read-only: tidak ada tambah/ubah/hapus dari aplikasi ini.
     */
    protected function canManage(): bool
    {
        return false;
    }

    /**
     * Siswa tidak punya kolom tahun ajaran sendiri; penandanya adalah
     * penempatan kelas di tabel siswa_rombel. Jadi daftar hanya menampilkan
     * siswa yang punya penempatan pada tahun ajaran yang dipilih di topbar
     * (lihat relasi rombelSaatIni di App\Models\Siswa).
     */
    protected function baseQuery()
    {
        return parent::baseQuery()
            ->when($this->tahunAjaranTerpilih()->id(), fn ($q) => $q->whereHas('rombelSaatIni'));
    }

    protected function fields(): array
    {
        return [
            ['name' => 'nisn', 'label' => 'NISN', 'type' => 'text'],
            ['name' => 'nis', 'label' => 'NIS', 'type' => 'text'],
            ['name' => 'nama_siswa', 'label' => 'Nama Lengkap', 'type' => 'text'],
            ['name' => 'jenis_kelamin', 'label' => 'Jenis Kelamin', 'type' => 'select', 'options' => ['L' => 'Laki-laki', 'P' => 'Perempuan']],
            ['name' => 'tempat_lahir', 'label' => 'Tempat Lahir', 'type' => 'text', 'list' => false],
            ['name' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'type' => 'date', 'list' => false],
            ['name' => 'agama', 'label' => 'Agama', 'type' => 'text', 'list' => false],
            ['name' => 'alamat', 'label' => 'Alamat', 'type' => 'textarea', 'list' => false],
            ['name' => 'nomor_hp', 'label' => 'No. HP', 'type' => 'text', 'list' => false],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'list' => false],
            ['name' => 'nama_ayah', 'label' => 'Nama Ayah', 'type' => 'text', 'list' => false],
            ['name' => 'nama_ibu', 'label' => 'Nama Ibu', 'type' => 'text', 'list' => false],
            ['name' => 'rombelSaatIni.kelas', 'label' => 'Kelas', 'type' => 'select', 'relation' => ['method' => 'rombelSaatIni.kelas', 'display' => 'nama_rombel']],
            ['name' => 'status_siswa', 'label' => 'Status', 'type' => 'select', 'options' => ['Aktif' => 'Aktif', 'Lulus' => 'Lulus', 'Keluar' => 'Keluar']],
            ['name' => 'foto', 'label' => 'Foto', 'type' => 'file', 'list' => false],
        ];
    }
}
