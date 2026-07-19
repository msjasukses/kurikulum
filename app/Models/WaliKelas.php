<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Datacenter tidak punya tabel wali_kelas tersendiri — penunjukan wali
 * kelas nempel langsung di kolom rombongan_belajar.wali_kelas_id. Model
 * ini menunjuk ke tabel yang sama dengan App\Models\Kelas (read-only),
 * tapi dipakai khusus untuk menampilkan rombel yang sudah punya wali kelas.
 */
class WaliKelas extends Model
{
    use HasFactory;

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

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }
}
