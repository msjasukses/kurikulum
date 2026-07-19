<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration awal (0002_01_01_000001_create_tingkat_kelas_table) sudah
     * mendefinisikan kolom "nama_tingkat", tapi tabel tingkat_kelas yang
     * sebenarnya ada di database ini ternyata masih memakai nama lama
     * "nama_kelas" (dibuat dari versi migration sebelum diperbaiki, dan
     * belum pernah di-migrate ulang). Akibatnya semua kode yang
     * mengasumsikan "nama_tingkat" (TingkatKelasController, relasi Kelas &
     * Alokasi Jam Mapel ke Tingkat Kelas, seeder) gagal dengan error
     * "Unknown column 'nama_tingkat'".
     *
     * Migration ini menyamakan nama kolom di database dengan yang sudah
     * diasumsikan di seluruh kode aplikasi, tanpa kehilangan data yang
     * sudah ada (rename kolom, bukan drop+create).
     */
    public function up(): void
    {
        if (Schema::hasColumn('tingkat_kelas', 'nama_kelas') && ! Schema::hasColumn('tingkat_kelas', 'nama_tingkat')) {
            Schema::table('tingkat_kelas', function (Blueprint $table) {
                $table->renameColumn('nama_kelas', 'nama_tingkat');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tingkat_kelas', 'nama_tingkat') && ! Schema::hasColumn('tingkat_kelas', 'nama_kelas')) {
            Schema::table('tingkat_kelas', function (Blueprint $table) {
                $table->renameColumn('nama_tingkat', 'nama_kelas');
            });
        }
    }
};
