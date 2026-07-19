<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('alokasi_jam_mapel');
    }
};
