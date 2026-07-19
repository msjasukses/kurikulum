<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TingkatKelas extends Model
{
    use HasFactory;

    /**
     * Data Tingkat Kelas diambil dari database "datacenter" (read-only),
     * bukan dari database lokal "kurikulum". Lihat config/database.php.
     */
    protected $connection = 'datacenter';

    protected $table = 'tingkat_kelas';

    protected $fillable = [
        'kode',
        'nama',
        'nomor',
        'jenjang',
        'urutan',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];
}
