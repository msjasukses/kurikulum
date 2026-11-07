<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku_digital_bab', function (Blueprint $table) {
            $table->id();

            $table->foreignId('buku_digital_id')
                ->constrained('buku_digital')
                ->cascadeOnDelete();

            $table->string('nomor_bab', 20)->nullable();
            $table->string('judul_bab', 300);
            $table->text('kata_kunci')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);

            // Asal data: "api" dari katalog SIBI (tepercaya) atau "pdf" dari
            // hasil pembacaan Daftar Isi berkas (perkiraan, bisa meleset).
            $table->string('sumber', 10)->default('api')->index();

            $table->timestamps();

            $table->unique(['buku_digital_id', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_digital_bab');
    }
};
