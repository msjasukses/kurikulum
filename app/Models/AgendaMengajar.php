<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Jurnal/agenda mengajar guru. Disimpan di database lokal "kurikulum";
 * data master (guru, kelas, mapel, siswa) dibaca dari database datacenter.
 */
class AgendaMengajar extends Model
{
    use HasFactory;

    protected $table = 'agenda_mengajar';

    public const STATUS_DITINJAU = 'Sedang Ditinjau';
    public const STATUS_DISETUJUI = 'Disetujui';
    public const STATUS_DITOLAK = 'Ditolak';

    /** Pilihan status kehadiran siswa pada satu jam pelajaran. */
    public const STATUS_KEHADIRAN = ['Hadir', 'Tidak Hadir', 'Sakit', 'Izin', 'Dispensasi'];

    /** Warna badge tiap status, dipakai di form dan daftar agenda. */
    public const WARNA_KEHADIRAN = [
        'Hadir' => 'success',
        'Tidak Hadir' => 'danger',
        'Sakit' => 'warning text-dark',
        'Izin' => 'info text-dark',
        'Dispensasi' => 'secondary',
    ];

    protected $fillable = [
        'guru_id',
        'mengajar_sebagai',
        'waktu_pengisian',
        'jam_ke',
        'jumlah_jam',
        'kelas_id',
        'paralel',
        'mata_pelajaran_id',
        'jumlah_siswa',
        'hadir',
        'absen',
        'siswa_absen',
        'kehadiran_siswa',
        'modul_ajar_id',
        'materi',
        'catatan',
        'photo',
        'status',
        'tahun_ajaran',
    ];

    protected $casts = [
        'waktu_pengisian' => 'datetime',
        'kehadiran_siswa' => 'array',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function modulAjar()
    {
        return $this->belongsTo(ModulAjar::class);
    }
}
