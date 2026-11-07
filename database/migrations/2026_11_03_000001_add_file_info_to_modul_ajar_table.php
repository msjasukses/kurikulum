<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keterangan berkas untuk modul ajar hasil unggahan. Kolom `file` sudah
     * ada sejak awal (berisi path di disk `public`), tetapi nama asli dan
     * ukuran berkas perlu disimpan supaya daftar modul bisa menampilkannya
     * apa adanya seperti saat guru mengunggah.
     */
    public function up(): void
    {
        Schema::table('modul_ajar', function (Blueprint $table) {
            $table->string('file_nama')->nullable()->after('file');
            $table->unsignedBigInteger('file_ukuran')->nullable()->after('file_nama');
        });
    }

    public function down(): void
    {
        Schema::table('modul_ajar', function (Blueprint $table) {
            $table->dropColumn(['file_nama', 'file_ukuran']);
        });
    }
};
