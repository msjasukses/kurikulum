<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom tambahan untuk fitur Generator Modul Ajar Digital: identitas
     * pertemuan, rujukan CP/TP/ATP dari pemetaan, isi modul per bagian,
     * dan bagian-bagian LKPD.
     */
    public function up(): void
    {
        Schema::table('modul_ajar', function (Blueprint $table) {
            $table->integer('jumlah_jam')->nullable()->after('fase');
            $table->integer('pertemuan_ke')->nullable()->after('jumlah_jam');
            $table->string('semester')->nullable()->after('pertemuan_ke');
            $table->unsignedBigInteger('pemetaan_cp_tp_atp_id')->nullable()->after('semester');
            $table->longText('capaian_pembelajaran')->nullable()->after('pemetaan_cp_tp_atp_id');
            $table->longText('tujuan_pembelajaran')->nullable()->after('capaian_pembelajaran');
            $table->longText('alur_tujuan_pembelajaran')->nullable()->after('tujuan_pembelajaran');
            $table->longText('kegiatan_pembelajaran')->nullable()->after('alur_tujuan_pembelajaran');
            $table->longText('target_peserta_didik')->nullable()->after('kegiatan_pembelajaran');
            $table->longText('model_pembelajaran')->nullable()->after('target_peserta_didik');
            $table->longText('pertanyaan_pemantik')->nullable()->after('model_pembelajaran');
            $table->longText('pendahuluan')->nullable()->after('pertanyaan_pemantik');
            $table->longText('kegiatan_inti')->nullable()->after('pendahuluan');
            $table->longText('penutup')->nullable()->after('kegiatan_inti');
            $table->longText('asesmen_diagnostik')->nullable()->after('penutup');
            $table->longText('asesmen_formatif')->nullable()->after('asesmen_diagnostik');
            $table->longText('asesmen_sumatif')->nullable()->after('asesmen_formatif');
            $table->longText('lkpd_tujuan')->nullable()->after('asesmen_sumatif');
            $table->longText('lkpd_petunjuk')->nullable()->after('lkpd_tujuan');
            $table->longText('lkpd_kegiatan')->nullable()->after('lkpd_petunjuk');
            $table->longText('lkpd_refleksi')->nullable()->after('lkpd_kegiatan');
            $table->longText('lkpd_asesmen')->nullable()->after('lkpd_refleksi');
        });
    }

    public function down(): void
    {
        Schema::table('modul_ajar', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_jam', 'pertemuan_ke', 'semester', 'pemetaan_cp_tp_atp_id',
                'capaian_pembelajaran', 'tujuan_pembelajaran', 'alur_tujuan_pembelajaran',
                'kegiatan_pembelajaran', 'target_peserta_didik', 'model_pembelajaran',
                'pertanyaan_pemantik', 'pendahuluan', 'kegiatan_inti', 'penutup',
                'asesmen_diagnostik', 'asesmen_formatif', 'asesmen_sumatif',
                'lkpd_tujuan', 'lkpd_petunjuk', 'lkpd_kegiatan', 'lkpd_refleksi', 'lkpd_asesmen',
            ]);
        });
    }
};
