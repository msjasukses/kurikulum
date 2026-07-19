<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    /**
     * Data Tahun Ajaran diambil dari database "datacenter" (read-only),
     * bukan dari database lokal "kurikulum". Lihat config/database.php.
     */
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
