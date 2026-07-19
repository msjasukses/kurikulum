<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Tabel penghubung siswa <-> rombongan_belajar per tahun ajaran, pada
 * database "datacenter" (read-only). Siswa tidak punya kolom kelas_id
 * langsung; penempatan kelasnya dicatat di sini.
 */
class SiswaRombel extends Model
{
    use HasFactory;

    protected $connection = 'datacenter';

    protected $table = 'siswa_rombel';

    protected $fillable = [
        'siswa_id',
        'rombongan_belajar_id',
        'tahun_ajaran_id',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
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
