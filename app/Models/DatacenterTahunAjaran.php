<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Tabel tahun_ajaran pada database "datacenter" (read-only) — dipakai
 * sebagai referensi relasi tahun_ajaran_id pada Kelas/Rombongan Belajar.
 *
 * Berbeda dari App\Models\TahunAjaran, yang menunjuk ke tabel tahun_ajaran
 * lokal (database "kurikulum") dan dipakai oleh menu Setting > Tahun Ajaran.
 */
class DatacenterTahunAjaran extends Model
{
    use HasFactory;

    protected $connection = 'datacenter';

    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'kode_tahun_ajaran',
        'nama_tahun_ajaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_aktif',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_aktif' => 'boolean',
    ];
}
