<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_mengajar', function (Blueprint $table) {
            // pegawai_id (guru lokal) diganti guru_id yang merujuk ke
            // App\Models\Guru (database datacenter, tanpa foreign key
            // karena beda koneksi/database). jam_mulai & jam_selesai
            // dihapus, digantikan referensi ke jam_mengajar (lokal).
            $table->dropColumn(['pegawai_id', 'jam_mulai', 'jam_selesai']);

            $table->unsignedBigInteger('guru_id')->nullable()->after('hari');

            $table->foreignId('jam_mengajar_id')->nullable()->after('guru_id')
                ->constrained('jam_mengajar')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_mengajar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jam_mengajar_id');
            $table->dropColumn('guru_id');

            $table->unsignedBigInteger('pegawai_id')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
        });
    }
};
