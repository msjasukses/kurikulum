<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Data guru diambil dari tabel "guru" pada database "datacenter" (read-only).
 * Dipakai sebagai sumber menu Data Pegawai (Kepegawaian > Data Pegawai) —
 * catatan: datacenter tidak punya tabel pegawai umum, hanya tabel guru,
 * jadi menu ini kini hanya mencakup guru/PTK, bukan staf non-guru.
 *
 * Kolom login/keamanan (password, remember_token, otp_*, session, dst.)
 * sengaja tidak dimasukkan ke $fillable karena menu ini read-only dan
 * tidak seharusnya menampilkan/menyentuh data kredensial guru.
 */
class Guru extends Model
{
    use HasFactory;

    protected $connection = 'datacenter';

    protected $table = 'guru';

    protected $fillable = [
        'nip',
        'nama_ptk',
        'email',
        'nomor_hp',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'jabatan',
        'status_kepegawaian',
        'foto',
        'is_aktif',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_aktif' => 'boolean',
    ];
}
