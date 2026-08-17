<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karakter_dpl', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            // "7 KAIH" (7 Kebiasaan Anak Indonesia Hebat) atau "DPL"
            // (Dimensi Profil Lulusan).
            $table->string('kategori')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karakter_dpl');
    }
};
