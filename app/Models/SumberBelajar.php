<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SumberBelajar extends Model
{
    use HasFactory;

    protected $table = 'sumber_belajar';

    protected $fillable = [
        'nama',
        'jenis',
        'keterangan',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];
}
