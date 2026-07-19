<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menu Alokasi Jam Mapel dihidupkan kembali (tabel lama sudah di-drop di
     * migration 0002_01_01_000026). Tanpa foreign key: tingkat_kelas dan
     * mata_pelajaran bersumber dari database datacenter (koneksi terpisah).
     */
    public function up(): void
    {
        Schema::create('alokasi_jam_mapel', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tingkat_kelas_id')->nullable();
            $table->unsignedBigInteger('mata_pelajaran_id')->nullable();
            $table->unsignedBigInteger('jurusan_id')->nullable();
            $table->integer('jumlah_kelas')->default(0);
            $table->integer('jumlah_jam_per_minggu')->default(0);
            $table->string('semester')->default('Ganjil');
            $table->string('tahun_ajaran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alokasi_jam_mapel');
    }
};
