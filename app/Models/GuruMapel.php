<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuruMapel extends Model
{
    use HasFactory;

    /**
     * Data Guru Mata Pelajaran diambil dari database "datacenter"
     * (read-only), bukan dari database lokal "kurikulum".
     * Lihat config/database.php.
     */
    protected $connection = 'datacenter';

    protected $table = 'guru_mapel';

    protected $fillable = [
        'guru_id',
        'mata_pelajaran_id',
        'rombongan_belajar_id',
        'tahun_ajaran_id',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'rombongan_belajar_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }
}
