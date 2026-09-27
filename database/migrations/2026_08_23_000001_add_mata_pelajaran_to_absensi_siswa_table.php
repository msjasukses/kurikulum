<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_siswa', function (Blueprint $table) {
            // Absensi dicatat per mata pelajaran (per jam pelajaran), bukan
            // lagi satu baris per hari. Tanpa foreign key karena mata
            // pelajaran bersumber dari database datacenter.
            $table->unsignedBigInteger('mata_pelajaran_id')->nullable()->after('kelas_id');
            $table->index(['kelas_id', 'tanggal', 'mata_pelajaran_id'], 'absensi_kelas_tanggal_mapel');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_siswa', function (Blueprint $table) {
            $table->dropIndex('absensi_kelas_tanggal_mapel');
            $table->dropColumn('mata_pelajaran_id');
        });
    }
};
