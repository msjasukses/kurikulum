<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jam_mengajar', function (Blueprint $table) {
            // Tanpa foreign key: tingkat_kelas sekarang bersumber dari
            // database datacenter (koneksi terpisah), sama seperti pola
            // tingkat_kelas_id di tabel alokasi_jam_mapel & jadwal_mengajar.
            $table->unsignedBigInteger('tingkat_kelas_id')->nullable()->after('jam_ke');
            $table->string('hari')->nullable()->after('tingkat_kelas_id');
        });
    }

    public function down(): void
    {
        Schema::table('jam_mengajar', function (Blueprint $table) {
            $table->dropColumn(['tingkat_kelas_id', 'hari']);
        });
    }
};
