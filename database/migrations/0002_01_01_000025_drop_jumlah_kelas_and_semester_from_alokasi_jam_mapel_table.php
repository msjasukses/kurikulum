<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membatalkan kolom jumlah_kelas & semester yang ditambahkan lewat
     * migration 0002_01_01_000023 — halaman Tambah/Ubah Alokasi Jam Mapel
     * dikembalikan seperti semula (tanpa kedua kolom ini).
     */
    public function up(): void
    {
        Schema::table('alokasi_jam_mapel', function (Blueprint $table) {
            if (Schema::hasColumn('alokasi_jam_mapel', 'jumlah_kelas')) {
                $table->dropColumn('jumlah_kelas');
            }
            if (Schema::hasColumn('alokasi_jam_mapel', 'semester')) {
                $table->dropColumn('semester');
            }
        });
    }

    public function down(): void
    {
        Schema::table('alokasi_jam_mapel', function (Blueprint $table) {
            $table->integer('jumlah_kelas')->default(1)->after('jumlah_jam_per_minggu');
            $table->string('semester')->nullable()->after('jumlah_kelas');
        });
    }
};
