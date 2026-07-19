<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_mengajar', function (Blueprint $table) {
            $table->id();
            // Guru merujuk ke tabel "guru" di database datacenter (tanpa FK lintas DB).
            $table->unsignedBigInteger('guru_id');
            $table->string('mengajar_sebagai', 30)->default('normal'); // normal / pengganti
            $table->dateTime('waktu_pengisian');
            $table->string('jam_ke', 20)->nullable();      // mis. "1-3" atau "5"
            $table->unsignedTinyInteger('jumlah_jam')->default(1);
            $table->unsignedBigInteger('kelas_id');        // rombongan_belajar datacenter
            $table->string('paralel', 10)->nullable();
            $table->unsignedBigInteger('mata_pelajaran_id');
            $table->unsignedSmallInteger('jumlah_siswa')->default(0);
            $table->unsignedSmallInteger('hadir')->default(0);
            $table->unsignedSmallInteger('absen')->default(0);
            $table->text('siswa_absen')->nullable();       // daftar nama siswa tidak hadir
            $table->unsignedBigInteger('modul_ajar_id')->nullable();
            $table->text('materi')->nullable();
            $table->text('catatan')->nullable();
            $table->string('photo')->nullable();
            $table->string('status', 30)->default('Sedang Ditinjau'); // Sedang Ditinjau / Disetujui / Ditolak
            $table->string('tahun_ajaran', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_mengajar');
    }
};
