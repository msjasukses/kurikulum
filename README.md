# SIM Kurikulum Sekolah

Aplikasi web manajemen kurikulum sekolah berbasis **Laravel 10**: penjadwalan, pemetaan
CP-TP-ATP, modul ajar digital (dengan bantuan AI), agenda mengajar, ruang belajar, dan
absensi per mata pelajaran — dalam satu sistem dengan tiga peran pengguna: **admin, guru,
dan siswa**.

Data induk (guru, siswa, kelas, mata pelajaran, tahun ajaran) dibaca dari aplikasi
**Data Center** sekolah, sehingga tidak perlu input ulang.

---

## Fitur

### Master Data
- Identitas sekolah, jam mengajar (dengan **import Excel**), jenis ekstrakurikuler
- Mata pelajaran, tingkat kelas, jurusan, tahun ajaran, kelas/rombel
- Master pendukung pembelajaran: semester, model pembelajaran, sumber belajar,
  dan karakter 7 KAIH / Dimensi Profil Lulusan

### Kepegawaian & Kesiswaan
- Data pegawai, wali kelas, dan penugasan guru mata pelajaran
- Data siswa dan orang tua (mengikuti penempatan rombel per tahun ajaran)

### Kurikulum
- **Jadwal Mengajar Guru** — pilihan jam mengajar berupa centang ganda; satu kali simpan
  membuat satu baris jadwal per jam pelajaran, dan pilihan jamnya menyesuaikan hari + kelas
- **Agenda Mengajar** — jurnal mengajar harian; memilih jam mengajar otomatis mengisi
  jam ke, kelas, mata pelajaran, dan daftar siswa, lengkap dengan status kehadiran per siswa
  (Hadir, Tidak Hadir, Sakit, Izin, Dispensasi) serta validasi agenda oleh admin
- **Pemetaan CP-TP-ATP** — elemen, CP, TP, ATP, indikator KKTP, model pembelajaran,
  sumber belajar, karakter 7 KAIH/DPL, dan semester; dilengkapi **import Excel**,
  filter (tingkat/mapel/semester), serta **cetak PDF & Word** yang mengikuti filter
- **Modul Ajar Digital** — penyusun modul ajar + LKPD dengan **bantuan AI**, unduh
  PDF/Word/LKPD
- **Alokasi Jam Mapel** dan **Analisis Kebutuhan Guru** (ekspor Excel)

### Ruang Belajar
- **Materi Online** — lampiran file dan tautan video, dengan penanda kelengkapan materi
- **Tugas** — pemberian tugas per kelas, pengumpulan tugas oleh siswa, penilaian oleh guru,
  serta penanda jumlah pengumpulan dan yang perlu dinilai

### Absensi
- Pencatatan kehadiran **per mata pelajaran** (bukan per hari), diisi guru pengampu
- Rekap per kelas (dengan rincian tiap mata pelajaran) dan rekap per siswa
  (ringkasan kehadiran beserta persentase tiap mata pelajaran)

### Lainnya
- Pemilih **tahun ajaran** di kanan atas yang menjadi sudut pandang seluruh data
- Dashboard berbeda per peran; siswa mendapat penanda tugas yang belum dikumpulkan
- Editor teks kaya (TinyMCE) pada isian panjang: pemetaan, modul ajar, materi, dan tugas

---

## Arsitektur Data

Aplikasi memakai **dua koneksi database**:

| Koneksi | Isi | Sifat |
|---|---|---|
| `mysql` (default) | Data milik aplikasi ini: jadwal, agenda, pemetaan CP-TP-ATP, modul ajar, materi, tugas, pengumpulan tugas, absensi, user | Baca & tulis |
| `datacenter` | Data induk dari aplikasi Data Center: guru, siswa, penempatan rombel, kelas, mata pelajaran, jurusan, tingkat kelas, tahun ajaran | **Hanya baca** |

Karena berbeda database, relasi lintas koneksi tidak memakai *foreign key* — hanya
menyimpan id-nya saja.

## Peran Pengguna

| Peran | Akses |
|---|---|
| **Admin** | Seluruh menu: master data, kepegawaian, kesiswaan, kurikulum, ruang belajar, absensi, manajemen user |
| **Guru** | Kurikulum (jadwal, agenda, CP-TP-ATP, modul ajar), ruang belajar (materi & tugas), absensi. Pilihan mapel dan kelas dibatasi sesuai penugasannya |
| **Siswa** | Materi dan tugas kelasnya, pengumpulan tugas, serta rekap kehadiran pribadi |

**Cara masuk:**
- Admin dan akun lokal → memakai **email** dan kata sandi yang dibuat di menu Manajemen User
- Guru → memakai **NIP**, siswa → memakai **NISN**, dengan kata sandi yang sama seperti di
  aplikasi Data Center. Akun lokalnya dibuat dan disinkronkan otomatis saat login pertama

---

## Kebutuhan Sistem

- PHP **8.1** atau lebih baru (disarankan 8.2+)
- MySQL / MariaDB
- Composer 2
- Ekstensi PHP umum Laravel: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`
- Koneksi internet untuk aset antarmuka (Bootstrap, jQuery, Select2, TinyMCE dimuat dari CDN)
  dan untuk fitur AI

> Tidak perlu Node.js/npm — antarmuka tidak memakai proses build.

---

## Instalasi

```bash
git clone <url-repositori-anda> kurikulum
cd kurikulum

composer install
cp .env.example .env
php artisan key:generate
```

Sunting `.env`, lalu siapkan database:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Buka `http://127.0.0.1:8000` di peramban.

### Pengaturan `.env`

```dotenv
APP_NAME="SIM Kurikulum Sekolah"
APP_URL=http://127.0.0.1:8000

# Database aplikasi ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kurikulum
DB_USERNAME=root
DB_PASSWORD=

# Database Data Center (hanya dibaca)
DATACENTER_DB_CONNECTION=mysql
DATACENTER_DB_HOST=127.0.0.1
DATACENTER_DB_PORT=3306
DATACENTER_DB_DATABASE=datacenter
DATACENTER_DB_USERNAME=root
DATACENTER_DB_PASSWORD=

# Opsional: kunci AI tingkat aplikasi (cadangan bila akun belum punya kunci sendiri)
ANTHROPIC_API_KEY=
ANTHROPIC_MODEL=claude-opus-5
```

### Akun Default

Setelah `php artisan migrate --seed`:

| Email | Kata Sandi | Peran |
|---|---|---|
| `admin@sekolah.sch.id` | `password` | Admin |

> Segera ganti kata sandinya lewat menu **Setting Profil** setelah masuk pertama kali.

---

## Fitur AI (Generate Modul Ajar)

Tombol **Generate Modul Ajar** di menu Modul Ajar Digital menyusun 14 bagian modul dan
LKPD berdasarkan mata pelajaran, tingkat, semester, alokasi jam, judul materi, serta
CP/TP/ATP yang dipilih dari Pemetaan.

Setiap akun **admin dan guru punya kunci API sendiri**, diisi di menu
**Setting Profil → Kunci API AI**. Tersedia tiga penyedia:

| Penyedia | Model bawaan | Halaman kunci |
|---|---|---|
| Anthropic (Claude) | `claude-opus-5` | https://console.anthropic.com/settings/keys |
| Groq | `llama-3.3-70b-versatile` | https://console.groq.com/keys |
| Google Gemini | `gemini-3.7-flash` | https://aistudio.google.com/apikey |

Catatan:
- Kunci disimpan **terenkripsi** (memakai `APP_KEY`) dan tidak pernah ditampilkan utuh lagi
- Urutan pemakaian: kunci milik akun → kunci aplikasi di `.env` → kerangka modul bawaan
  aplikasi (tanpa AI)
- Nama model bisa diganti sendiri di halaman profil bila penyedia memperbarui daftar modelnya

---

## Struktur Proyek

```
app/
├─ Http/Controllers/
│  ├─ Crud/BaseCrudController.php   # kerangka CRUD: form, tabel, filter, validasi
│  ├─ Absensi/  Kepegawaian/  Kesiswaan/  Kurikulum/  RuangBelajar/  Setting/  Users/
│  └─ Concerns/FilterPenugasanGuru.php  # pembatas data sesuai penugasan guru
├─ Models/                          # model lokal + model datacenter (read-only)
├─ Services/
│  ├─ GeneratorModulAjarAi.php      # penyusun modul ajar
│  └─ Ai/                           # penyedia AI: Anthropic, Groq, Gemini
├─ Imports/  Exports/               # import & template Excel
└─ Support/
   ├─ TahunAjaranTerpilih.php       # tahun ajaran aktif dari topbar
   └─ TeksKaya.php                  # penyaring & penampil HTML editor
resources/views/
├─ crud/                            # form & tabel generik seluruh menu CRUD
├─ partials/tinymce.blade.php       # konfigurasi editor teks kaya
├─ kurikulum/  absensi/  ruangbelajar/  settings/  users/  auth/
database/migrations/                # skema database aplikasi
```

Sebagian besar menu dibangun di atas `BaseCrudController`: cukup mendefinisikan
`fields()` untuk mendapatkan form, tabel, pencarian, filter, dan validasi. Tersedia pula
tipe kolom `checkboxes` (pilihan ganda), `computed` (kolom penanda di tabel), penanda
`editor` (TinyMCE), dan `multiple` (select yang tampil sebagai centang ganda).

---

## Lisensi

Dirilis di bawah lisensi [MIT](https://opensource.org/licenses/MIT).
