<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_eskul', function (Blueprint $table) {
            $table->id();
            $table->string('nama_eskul')->unique();
            $table->string('pembina')->nullable();
            $table->string('hari')->nullable();
            $table->string('jam')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_eskul');
    }
};
