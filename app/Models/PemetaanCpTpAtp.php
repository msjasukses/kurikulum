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
        'capaian_pembelajaran',
        'tujuan_pembelajaran',
        'alur_tujuan_pembelajaran',
        'tahun_ajaran',
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
