<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisEskul extends Model
{
    use HasFactory;

    protected $table = 'jenis_eskul';

    protected $fillable = [
        'nama_eskul',
        'pembina',
        'hari',
        'jam',
        'keterangan',
    ];
}
