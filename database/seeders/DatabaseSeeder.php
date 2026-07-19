<?php

namespace Database\Seeders;

use App\Models\IdentitasSekolah;
use App\Models\JamMengajar;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Hanya mengisi tabel di database lokal "kurikulum".
     * Data master (Tahun Ajaran, Tingkat Kelas, Jurusan, Kelas/Rombel,
     * Mata Pelajaran, dll.) bersumber dari database "datacenter" yang
     * read-only, sehingga tidak boleh di-seed dari aplikasi ini.
     */
    public function run(): void
    {
        // Akun admin default
        User::firstOrCreate(
            ['email' => 'admin@sekolah.sch.id'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'aktif' => true,
            ]
        );

        // Identitas sekolah default
        if (IdentitasSekolah::count() === 0) {
            IdentitasSekolah::create([
                'nama_sekolah' => 'SMA Contoh',
                'npsn' => '00000000',
                'alamat' => 'Jl. Pendidikan No. 1',
                'nama_kepala_sekolah' => 'Kepala Sekolah',
            ]);
        }

        // Jam mengajar contoh
        $jamData = [
            [1, '07:00', '07:45'],
            [2, '07:45', '08:30'],
            [3, '08:30', '09:15'],
        ];
        foreach ($jamData as [$ke, $mulai, $selesai]) {
            JamMengajar::firstOrCreate(['jam_ke' => $ke], [
                'nama' => "Jam ke-{$ke}",
                'jam_mulai' => $mulai,
                'jam_selesai' => $selesai,
            ]);
        }
    }
}
