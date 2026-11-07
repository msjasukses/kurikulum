<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu bab/topik materi pada sebuah buku digital.
 *
 * Datanya bisa berasal dari katalog SIBI (sumber "api", tepercaya) atau dari
 * pembacaan Daftar Isi berkas PDF (sumber "pdf", hasil perkiraan).
 */
class BukuDigitalBab extends Model
{
    use HasFactory;

    protected $table = 'buku_digital_bab';

    protected $fillable = [
        'buku_digital_id',
        'induk_id',
        'level',
        'nomor_bab',
        'judul_bab',
        'kata_kunci',
        'halaman',
        'urutan',
        'sumber',
    ];

    protected $casts = [
        'level' => 'integer',
        'halaman' => 'integer',
        'urutan' => 'integer',
    ];

    public function buku()
    {
        return $this->belongsTo(BukuDigital::class, 'buku_digital_id');
    }

    /** Bab induk dari sebuah sub bab. */
    public function induk()
    {
        return $this->belongsTo(self::class, 'induk_id');
    }

    /** Sub bab / sub materi di bawah bab ini. */
    public function subBab()
    {
        return $this->hasMany(self::class, 'induk_id')->orderBy('urutan');
    }

    /** Bab hasil baca PDF perlu ditandai karena akurasinya tidak dijamin. */
    public function dariPdf(): bool
    {
        return $this->sumber === 'pdf';
    }
}
