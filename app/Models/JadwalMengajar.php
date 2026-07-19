<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalMengajar extends Model
{
    use HasFactory;

    protected $table = 'jadwal_mengajar';

    protected $fillable = [
        'guru_id',
        'mata_pelajaran_id',
        'kelas_id',
        'hari',
        'jam_mengajar_id',
        'ruangan',
        'tahun_ajaran',
    ];

    /**
     * Guru bersumber dari database datacenter (App\Models\Guru), bukan lagi
     * dari tabel pegawai lokal.
     */
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
        return $this->belongsTo(Kelas::class);
    }

    public function jamMengajar()
    {
        return $this->belongsTo(JamMengajar::class);
    }
}
