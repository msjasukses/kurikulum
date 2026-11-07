<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buku_digital_bab', function (Blueprint $table) {
            // Sub bab menunjuk ke bab induknya di tabel yang sama. Dibuat
            // nullable karena bab utama tidak punya induk.
            $table->foreignId('induk_id')
                ->nullable()
                ->after('buku_digital_id')
                ->constrained('buku_digital_bab')
                ->cascadeOnDelete();

            // 1 = bab utama, 2 = sub bab / sub materi.
            $table->unsignedTinyInteger('level')->default(1)->after('induk_id')->index();

            // Nomor halaman awal dari Daftar Isi, bila terbaca.
            $table->unsignedSmallInteger('halaman')->nullable()->after('kata_kunci');
        });
    }

    public function down(): void
    {
        Schema::table('buku_digital_bab', function (Blueprint $table) {
            $table->dropForeign(['induk_id']);
            $table->dropColumn(['induk_id', 'level', 'halaman']);
        });
    }
};
