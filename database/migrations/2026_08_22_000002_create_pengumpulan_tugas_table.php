<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumpulan_tugas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tugas_id');
            // Tanpa foreign key: data siswa bersumber dari database datacenter
            // (koneksi terpisah), sama seperti pola kolom siswa_id lainnya.
            $table->unsignedBigInteger('siswa_id');
            $table->text('catatan')->nullable();
            $table->string('file')->nullable();
            $table->timestamp('dikumpulkan_pada')->nullable();
            $table->boolean('terlambat')->default(false);
            $table->unsignedTinyInteger('nilai')->nullable();
            $table->text('umpan_balik')->nullable();
            $table->timestamp('dinilai_pada')->nullable();
            $table->timestamps();

            // Satu siswa hanya punya satu baris pengumpulan per tugas.
            $table->unique(['tugas_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_tugas');
    }
};
