<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    /**
     * Data Kelas/Rombel diambil dari tabel "rombongan_belajar" pada database
     * "datacenter" (read-only), bukan dari database lokal "kurikulum".
     * Lihat config/database.php.
     */
    protected $connection = 'datacenter';

    protected $table = 'rombongan_belajar';

    protected $fillable = [
        'nama_rombel',
        'tingkat',
        'jurusan_id',
        'tahun_ajaran_id',
        'wali_kelas_id',
        'kapasitas',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }
}
