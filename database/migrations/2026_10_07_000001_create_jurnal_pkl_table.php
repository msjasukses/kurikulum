<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_pkl', function (Blueprint $table) {
            $table->id();
            // Tanpa foreign key: siswa dari datacenter dan jadwal magang dari
            // database Absensi (koneksi terpisah).
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('jadwal_magang_id');
            // Disalin dari jadwal saat diisi, supaya jurnal tetap terbaca
            // walau jadwal di aplikasi Absensi kemudian diubah/dihapus.
            $table->string('tempat', 150);
            $table->date('tanggal');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->text('kegiatan');
            $table->text('hasil')->nullable();
            $table->text('kendala')->nullable();
            $table->string('foto')->nullable();
            $table->string('status', 20)->default('Menunggu');
            $table->text('catatan_pembimbing')->nullable();
            $table->unsignedBigInteger('diperiksa_oleh')->nullable();
            $table->timestamp('diperiksa_pada')->nullable();
            $table->timestamps();

            // Satu jurnal per siswa per hari magang.
            $table->unique(['siswa_id', 'tanggal']);
            $table->index(['jadwal_magang_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_pkl');
    }
};
