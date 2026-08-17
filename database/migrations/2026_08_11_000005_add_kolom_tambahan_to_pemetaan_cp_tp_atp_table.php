<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pemetaan_cp_tp_atp', function (Blueprint $table) {
            $table->string('semester')->nullable()->after('fase');
            $table->text('elemen')->nullable()->after('semester');
            $table->text('indikator_kktp')->nullable()->after('alur_tujuan_pembelajaran');
            // Tiga kolom berikut menyimpan pilihan ganda (checkbox) sebagai
            // JSON berisi nama pilihan, mengacu ke tabel master masing-masing.
            $table->json('model_pembelajaran')->nullable()->after('indikator_kktp');
            $table->json('sumber_belajar')->nullable()->after('model_pembelajaran');
            $table->json('karakter_dpl')->nullable()->after('sumber_belajar');
        });
    }

    public function down(): void
    {
        Schema::table('pemetaan_cp_tp_atp', function (Blueprint $table) {
            $table->dropColumn([
                'semester',
                'elemen',
                'indikator_kktp',
                'model_pembelajaran',
                'sumber_belajar',
                'karakter_dpl',
            ]);
        });
    }
};
