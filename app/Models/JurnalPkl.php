<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Jurnal harian PKL/magang siswa. Disimpan di database lokal "kurikulum";
 * tanggal yang boleh diisi mengikuti jadwal magang di aplikasi Absensi.
 */
class JurnalPkl extends Model
{
    use HasFactory;

    protected $table = 'jurnal_pkl';

    public const STATUS_MENUNGGU = 'Menunggu';
    public const STATUS_DISETUJUI = 'Disetujui';
    public const STATUS_REVISI = 'Revisi';

    public const WARNA_STATUS = [
        self::STATUS_MENUNGGU => 'warning text-dark',
        self::STATUS_DISETUJUI => 'success',
        self::STATUS_REVISI => 'danger',
    ];

    protected $fillable = [
        'siswa_id',
        'jadwal_magang_id',
        'tempat',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'kegiatan',
        'hasil',
        'kendala',
        'foto',
        'status',
        'catatan_pembimbing',
        'diperiksa_oleh',
        'diperiksa_pada',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'diperiksa_pada' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jadwalMagang()
    {
        return $this->belongsTo(JadwalMagang::class);
    }

    public function pemeriksa()
    {
        return $this->belongsTo(User::class, 'diperiksa_oleh');
    }

    /** Siswa hanya boleh mengubah jurnal yang belum disetujui. */
    public function bisaDiubahSiswa(): bool
    {
        return $this->status !== self::STATUS_DISETUJUI;
    }

    public function warnaStatus(): string
    {
        return self::WARNA_STATUS[$this->status] ?? 'secondary';
    }
}
