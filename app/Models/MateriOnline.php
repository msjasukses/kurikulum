<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriOnline extends Model
{
    use HasFactory;

    protected $table = 'materi_online';

    protected $fillable = [
        'judul',
        'mata_pelajaran_id',
        'kelas_id',
        'pegawai_id',
        'file',
        'link_video',
        'deskripsi',
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /** Guru pengunggah — diambil dari tabel "guru" database datacenter. */
    public function pegawai()
    {
        return $this->belongsTo(Guru::class, 'pegawai_id');
    }
}
