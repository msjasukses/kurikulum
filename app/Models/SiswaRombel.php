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

    /**
     * Id kelas/rombel tempat seorang siswa terdaftar. Bila tahun ajaran
     * diisi, hanya penempatan pada tahun itu yang diambil — dipakai menu
     * Tugas dan dashboard supaya keduanya menghitung tugas yang sama.
     *
     * @return array<int, int>
     */
    public static function kelasIdsSiswa(?int $siswaId, ?int $tahunAjaranId = null): array
    {
        if ($siswaId === null) {
            return [];
        }

        return static::where('siswa_id', $siswaId)
            ->when($tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->pluck('rombongan_belajar_id')
            ->all();
    }
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
