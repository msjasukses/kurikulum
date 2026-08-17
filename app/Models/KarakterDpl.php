<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Master karakter yang dibiasakan dalam pembelajaran: 7 KAIH
 * (7 Kebiasaan Anak Indonesia Hebat) dan DPL (Dimensi Profil Lulusan).
 */
class KarakterDpl extends Model
{
    use HasFactory;

    protected $table = 'karakter_dpl';

    protected $fillable = [
        'nama',
        'kategori',
        'urutan',
        'deskripsi',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];
}
