<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    protected $fillable = [
        'judul',
        'mata_pelajaran_id',
        'kelas_id',
        'pegawai_id',
        'deskripsi',
        'file_lampiran',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /** Guru pemberi tugas — diambil dari tabel "guru" database datacenter. */
    public function pegawai()
    {
        return $this->belongsTo(Guru::class, 'pegawai_id');
    }
}
