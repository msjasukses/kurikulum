<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menu Alokasi Jam Mapel dihapus dari aplikasi, jadi tabelnya sekaligus
     * dibuang juga.
     */
    public function up(): void
    {
        Schema::dropIfExists('alokasi_jam_mapel');
    }

    public function down(): void
    {
        // Struktur dasar dari migration awal (0002_01_01_000015).
        Schema::create('alokasi_jam_mapel', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mata_pelajaran_id')->nullable();
            $table->unsignedBigInteger('tingkat_kelas_id')->nullable();
            $table->unsignedBigInteger('jurusan_id')->nullable();
            $table->integer('jumlah_jam_per_minggu')->default(0);
            $table->string('tahun_ajaran')->nullable();
            $table->timestamps();
        });
    }
};
