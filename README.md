# SIM Kurikulum Sekolah

Aplikasi manajemen sekolah berbasis Laravel 10, mencakup:

1. **Setting Sekolah** - Identitas Sekolah, Jam Mengajar, Mata Pelajaran, Jenis Eskul, Tingkat Kelas, Jurusan, Kelas/Rombel
2. **Kepegawaian** - Data Pegawai, Data Wali Kelas, Data Guru Mata Pelajaran
3. **Kesiswaan** - Data Siswa, Data Orang Tua
4. **Kurikulum** - Jadwal Mengajar Guru, Pemetaan CP-TP-ATP, Alokasi Jam Mapel, Modul Ajar Digital, Analisis Kebutuhan Guru
5. **Ruang Belajar** - Materi Online, Tugas
6. **Absensi** - Rekap per Kelas, Rekap per Siswa, Koreksi Absensi
7. **Manajemen User** - User Admin, User Guru, User Siswa

Hak akses dibagi 3 role: **admin** (akses penuh), **guru** (kurikulum, ruang belajar, absensi), **siswa** (lihat materi/tugas, lihat absensi sendiri).

## Catatan penting

Project ini berisi kode aplikasi lengkap (migration, model, controller, view, routes), TAPI folder `vendor/` (dependency Laravel) belum ter-install karena dibuat di lingkungan tanpa akses internet/composer. Ikuti langkah instalasi di bawah ini di komputer Anda (Laragon sudah memiliki PHP & Composer).

## Langkah Instalasi (di Laragon)

1. Buka terminal/cmd di folder project ini: `C:\laragon\www\kurikulum`

2. Install dependency PHP:
   ```
   composer install
   ```

3. Buat database MySQL bernama `kurikulum` (bisa lewat HeidiSQL/phpMyAdmin bawaan Laragon, atau `laragon.exe` > Menu > MySQL > buat database baru).

4. File `.env` sudah disediakan (menyalin dari `.env.example`) dengan konfigurasi database default Laragon (`root` tanpa password, database `kurikulum`). Sesuaikan jika perlu.

5. Generate application key:
   ```
   php artisan key:generate
   ```

6. Jalankan migrasi + data awal (seeder):
   ```
   php artisan migrate --seed
   ```

7. Buat symlink storage (agar file upload seperti foto & modul ajar bisa diakses via browser):
   ```
   php artisan storage:link
   ```

8. Akses aplikasi:
   - Jika pakai Laragon (Apache), buka `http://kurikulum.test` (Laragon otomatis membuatkan virtual host untuk folder di `www`), atau
   - Jalankan `php artisan serve` lalu buka `http://127.0.0.1:8000`

## Login Default

- Email: `admin@sekolah.sch.id`
- Password: `password`

**Segera ganti password ini setelah login pertama kali** (lewat menu Manajemen User > User Admin).

## Struktur Data yang Perlu Diisi Berurutan

Agar dropdown-dropdown terisi dengan benar, disarankan mengisi data dengan urutan berikut:

1. Setting Sekolah: Tingkat Kelas, Jurusan, Kelas/Rombel, Mata Pelajaran, Jam Mengajar
2. Kepegawaian: Data Pegawai, baru kemudian Data Wali Kelas & Data Guru Mata Pelajaran
3. Kesiswaan: Data Siswa (perlu Kelas), baru Data Orang Tua
4. Kurikulum & Ruang Belajar: menyusul setelah data pegawai, siswa, kelas, dan mapel tersedia
5. Manajemen User: buat akun login untuk guru & siswa, dihubungkan ke data Pegawai/Siswa yang bersangkutan

## Pengembangan Lanjutan

Kerangka CRUD dibangun dengan pola generik (`app/Http/Controllers/Crud/BaseCrudController.php`) yang bisa dipakai ulang untuk menambah modul baru dengan cepat: cukup buat Model, Migration, dan Controller singkat yang mendefinisikan `fields()`.

Beberapa fitur masih versi dasar dan bisa dikembangkan lebih lanjut sesuai kebutuhan:
- Modul Ajar Digital & Materi Online: saat ini hanya unggah file/link, belum ada versioning.
- Tugas: belum ada fitur pengumpulan & penilaian jawaban siswa.
- Analisis Kebutuhan Guru: perhitungan memakai asumsi 24 jam wajib mengajar per guru per minggu (bisa disesuaikan di `AnalisisKebutuhanGuruController`).
