<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemetaanCpTpAtp extends Model
{
    use HasFactory;

    protected $table = 'pemetaan_cp_tp_atp';

    protected $fillable = [
        'mata_pelajaran_id',
        'tingkat_kelas_id',
        'fase',
        'semester',
        'elemen',
        'capaian_pembelajaran',
        'tujuan_pembelajaran',
        'alur_tujuan_pembelajaran',
        'indikator_kktp',
        'model_pembelajaran',
        'sumber_belajar',
        'karakter_dpl',
        'tahun_ajaran',
    ];

    /**
     * Tiga kolom pilihan ganda disimpan sebagai JSON berisi nama pilihan
     * (mengacu ke master Model Pembelajaran, Sumber Belajar, dan Karakter
     * 7 KAIH/DPL) supaya isinya tetap terbaca meski master berubah.
     */
    protected $casts = [
        'model_pembelajaran' => 'array',
        'sumber_belajar' => 'array',
        'karakter_dpl' => 'array',
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function tingkatKelas()
    {
        return $this->belongsTo(TingkatKelas::class);
    }
}
