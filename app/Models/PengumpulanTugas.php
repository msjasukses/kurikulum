<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Pengumpulan tugas oleh siswa: satu baris per siswa per tugas, berisi
 * catatan, file jawaban, waktu pengumpulan, serta nilai & umpan balik guru.
 */
class PengumpulanTugas extends Model
{
    use HasFactory;

    protected $table = 'pengumpulan_tugas';

    protected $fillable = [
        'tugas_id',
        'siswa_id',
        'catatan',
        'file',
        'dikumpulkan_pada',
        'terlambat',
        'nilai',
        'umpan_balik',
        'dinilai_pada',
    ];

    protected $casts = [
        'dikumpulkan_pada' => 'datetime',
        'dinilai_pada' => 'datetime',
        'terlambat' => 'boolean',
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    /** Sudah dinilai guru, sehingga siswa tidak boleh mengubah jawabannya lagi. */
    public function sudahDinilai(): bool
    {
        return $this->nilai !== null;
    }
}
