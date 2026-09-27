<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_mengajar', function (Blueprint $table) {
            // Rincian kehadiran per siswa: [{siswa_id, nama, status}] dengan
            // status Hadir/Tidak Hadir/Sakit/Izin/Dispensasi. Kolom siswa_absen
            // tetap dipakai sebagai ringkasan teks untuk daftar & pencarian.
            $table->json('kehadiran_siswa')->nullable()->after('siswa_absen');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_mengajar', function (Blueprint $table) {
            $table->dropColumn('kehadiran_siswa');
        });
    }
};
