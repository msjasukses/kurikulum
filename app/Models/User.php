<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_ADMIN = 'admin';
    const ROLE_GURU = 'guru';
    const ROLE_SISWA = 'siswa';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'pegawai_id',
        'guru_id',
        'siswa_id',
        'aktif',
        'ai_provider',
        'ai_api_key',
        'ai_model',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'ai_api_key',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'aktif' => 'boolean',
        // Kunci API AI disimpan terenkripsi memakai APP_KEY.
        'ai_api_key' => 'encrypted',
    ];

    /** Akun ini punya kunci API AI sendiri. */
    public function punyaKunciAi(): bool
    {
        return filled($this->ai_api_key);
    }

    /** Empat huruf terakhir kunci untuk ditampilkan di halaman profil. */
    public function petunjukKunciAi(): ?string
    {
        $kunci = (string) $this->ai_api_key;

        return $kunci === '' ? null : str_repeat('•', 8).substr($kunci, -4);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    /** Guru datacenter yang tertaut ke akun ini (login via NIP datacenter). */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isGuru(): bool
    {
        return $this->role === self::ROLE_GURU;
    }

    public function isSiswa(): bool
    {
        return $this->role === self::ROLE_SISWA;
    }
}
