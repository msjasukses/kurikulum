<?php

use App\Http\Controllers\Absensi\KoreksiController;
use App\Http\Controllers\Absensi\RekapKelasController;
use App\Http\Controllers\Absensi\RekapSiswaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Kepegawaian\GuruMapelController;
use App\Http\Controllers\Kepegawaian\PegawaiController;
use App\Http\Controllers\Kepegawaian\WaliKelasController;
use App\Http\Controllers\Kesiswaan\OrangTuaController;
use App\Http\Controllers\Kesiswaan\SiswaController;
use App\Http\Controllers\Kurikulum\AgendaMengajarController;
use App\Http\Controllers\Kurikulum\AlokasiJamMapelController;
use App\Http\Controllers\Kurikulum\AnalisisKebutuhanGuruController;
use App\Http\Controllers\Kurikulum\JadwalMengajarController;
use App\Http\Controllers\Kurikulum\ModulAjarController;
use App\Http\Controllers\Kurikulum\PemetaanCpTpAtpController;
use App\Http\Controllers\Kurikulum\PemetaanCpTpAtpImportController;
use App\Http\Controllers\PilihTahunAjaranController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RuangBelajar\MateriOnlineController;
use App\Http\Controllers\RuangBelajar\TugasController;
use App\Http\Controllers\Setting\IdentitasSekolahController;
use App\Http\Controllers\Setting\JamMengajarController;
use App\Http\Controllers\Setting\JenisEskulController;
use App\Http\Controllers\Setting\JurusanController;
use App\Http\Controllers\Setting\KelasController;
use App\Http\Controllers\Setting\MataPelajaranController;
use App\Http\Controllers\Setting\TingkatKelasController;
use App\Http\Controllers\Setting\TahunAjaranController;
use App\Http\Controllers\Users\UserAdminController;
use App\Http\Controllers\Users\UserGuruController;
use App\Http\Controllers\Users\UserSiswaController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Penukar tahun ajaran (dropdown topbar) — semua role, hanya mengubah
    // sudut pandang data, bukan hak akses.
    Route::post('/tahun-ajaran/pilih', [PilihTahunAjaranController::class, 'store'])->name('tahun-ajaran.pilih');

    // ================= Setting Profil (semua role, hanya data sendiri) =================
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfilController::class, 'updatePassword'])->name('profil.password');

    // ================= Master Data (admin) =================
    Route::middleware('role:admin')->prefix('setting')->name('setting.')->group(function () {
        Route::get('identitas', [IdentitasSekolahController::class, 'edit'])->name('identitas.edit');
        Route::put('identitas', [IdentitasSekolahController::class, 'update'])->name('identitas.update');

        Route::resource('jam-mengajar', JamMengajarController::class)->except(['show'])->names('jam-mengajar');
        Route::resource('mata-pelajaran', MataPelajaranController::class)->except(['show'])->names('mata-pelajaran');
        Route::resource('jenis-eskul', JenisEskulController::class)->except(['show'])->names('jenis-eskul');
        Route::resource('tingkat-kelas', TingkatKelasController::class)->except(['show'])->names('tingkat-kelas');
        Route::resource('jurusan', JurusanController::class)->except(['show'])->names('jurusan');
        Route::resource('tahun-ajaran', TahunAjaranController::class)->except(['show'])->names('tahun-ajaran');
        Route::resource('kelas', KelasController::class)->except(['show'])->names('kelas');
    });

    // ================= Kepegawaian (admin) =================
    Route::middleware('role:admin')->prefix('kepegawaian')->name('kepegawaian.')->group(function () {
        Route::resource('pegawai', PegawaiController::class)->except(['show'])->names('pegawai');
        Route::resource('wali-kelas', WaliKelasController::class)->except(['show'])->names('wali-kelas');
        Route::resource('guru-mapel', GuruMapelController::class)->except(['show'])->names('guru-mapel');
    });

    // ================= Kesiswaan (admin) =================
    Route::middleware('role:admin')->prefix('kesiswaan')->name('kesiswaan.')->group(function () {
        Route::resource('siswa', SiswaController::class)->except(['show'])->names('siswa');
        Route::resource('orang-tua', OrangTuaController::class)->except(['show', 'index'])->names('orang-tua');
    });
    // Daftar orang tua boleh dilihat siswa (read-only, hanya data dirinya).
    Route::middleware('role:admin,siswa')->prefix('kesiswaan')->name('kesiswaan.')->group(function () {
        Route::get('orang-tua', [OrangTuaController::class, 'index'])->name('orang-tua.index');
    });

    // ================= Kurikulum (admin & guru) =================
    Route::middleware('role:admin,guru')->prefix('kurikulum')->name('kurikulum.')->group(function () {
        Route::resource('jadwal', JadwalMengajarController::class)->except(['show'])->names('jadwal');
        Route::get('agenda', [AgendaMengajarController::class, 'index'])->name('agenda.index');
        Route::get('agenda/tambah', [AgendaMengajarController::class, 'create'])->name('agenda.create');
        Route::post('agenda', [AgendaMengajarController::class, 'store'])->name('agenda.store');
        Route::get('agenda/{id}/edit', [AgendaMengajarController::class, 'edit'])->name('agenda.edit');
        Route::put('agenda/{id}', [AgendaMengajarController::class, 'update'])->name('agenda.update');
        Route::post('agenda/{id}/status', [AgendaMengajarController::class, 'setStatus'])->name('agenda.status');
        Route::delete('agenda/{id}', [AgendaMengajarController::class, 'destroy'])->name('agenda.destroy');
        Route::resource('cp-tp-atp', PemetaanCpTpAtpController::class)->except(['show'])->names('cp-tp-atp');
        Route::get('cp-tp-atp-import', [PemetaanCpTpAtpImportController::class, 'form'])->name('cp-tp-atp-import.form');
        Route::post('cp-tp-atp-import', [PemetaanCpTpAtpImportController::class, 'import'])->name('cp-tp-atp-import.store');
        Route::get('cp-tp-atp-import/template', [PemetaanCpTpAtpImportController::class, 'template'])->name('cp-tp-atp-import.template');
        Route::get('cp-tp-atp-cetak/pdf', [PemetaanCpTpAtpController::class, 'pdf'])->name('cp-tp-atp-cetak.pdf');
        Route::get('cp-tp-atp-cetak/word', [PemetaanCpTpAtpController::class, 'word'])->name('cp-tp-atp-cetak.word');
        Route::get('modul-ajar', [ModulAjarController::class, 'index'])->name('modul-ajar.index');
        Route::post('modul-ajar', [ModulAjarController::class, 'store'])->name('modul-ajar.store');
        Route::delete('modul-ajar/{id}', [ModulAjarController::class, 'destroy'])->name('modul-ajar.destroy');
        Route::get('modul-ajar/{id}/pdf', [ModulAjarController::class, 'pdf'])->name('modul-ajar.pdf');
        Route::get('modul-ajar/{id}/word', [ModulAjarController::class, 'word'])->name('modul-ajar.word');
        Route::get('modul-ajar/{id}/lkpd', [ModulAjarController::class, 'lkpd'])->name('modul-ajar.lkpd');
    });
    Route::middleware('role:admin')->prefix('kurikulum')->name('kurikulum.')->group(function () {
        Route::get('alokasi-jam', [AlokasiJamMapelController::class, 'index'])->name('alokasi-jam.index');
        Route::post('alokasi-jam', [AlokasiJamMapelController::class, 'store'])->name('alokasi-jam.store');
        Route::put('alokasi-jam/{id}', [AlokasiJamMapelController::class, 'update'])->name('alokasi-jam.update');
        Route::delete('alokasi-jam/{id}', [AlokasiJamMapelController::class, 'destroy'])->name('alokasi-jam.destroy');
        Route::get('analisis-guru', [AnalisisKebutuhanGuruController::class, 'index'])->name('analisis-guru.index');
        Route::get('analisis-guru/excel', [AnalisisKebutuhanGuruController::class, 'excel'])->name('analisis-guru.excel');
    });

    // ================= Ruang Belajar (semua role, aksi kelola dibatasi di controller) =================
    Route::middleware('role:admin,guru,siswa')->prefix('ruangbelajar')->name('ruangbelajar.')->group(function () {
        Route::resource('materi', MateriOnlineController::class)->except(['show'])->names('materi');
        Route::resource('tugas', TugasController::class)->except(['show'])->names('tugas');
    });

    // ================= Absensi =================
    Route::middleware('role:admin,guru')->prefix('absensi')->name('absensi.')->group(function () {
        Route::get('rekap-kelas', [RekapKelasController::class, 'index'])->name('rekap-kelas.index');
        Route::get('koreksi', [KoreksiController::class, 'index'])->name('koreksi.index');
        Route::post('koreksi', [KoreksiController::class, 'store'])->name('koreksi.store');
    });
    Route::middleware('role:admin,guru,siswa')->prefix('absensi')->name('absensi.')->group(function () {
        Route::get('rekap-siswa', [RekapSiswaController::class, 'index'])->name('rekap-siswa.index');
    });

    // ================= Manajemen User (admin) =================
    Route::middleware('role:admin')->prefix('users')->name('users.')->group(function () {
        Route::resource('admin', UserAdminController::class)->except(['show'])->names('admin');
        Route::resource('guru', UserGuruController::class)->except(['show'])->names('guru');
        Route::resource('siswa', UserSiswaController::class)->except(['show'])->names('siswa');
    });
});
