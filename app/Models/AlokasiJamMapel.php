<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlokasiJamMapel extends Model
{
    use HasFactory;

    protected $table = 'alokasi_jam_mapel';

    protected $fillable = [
        'tingkat_kelas_id',
        'mata_pelajaran_id',
        'jurusan_id',
        'jumlah_kelas',
        'jumlah_jam_per_minggu',
        'semester',
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

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    /** Total JP = jam per minggu x jumlah kelas (rombel) pada tingkat tsb. */
    public function getTotalJpAttribute(): int
    {
        return (int) $this->jumlah_jam_per_minggu * (int) $this->jumlah_kelas;
    }
}
