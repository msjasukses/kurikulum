<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'model_pembelajaran';

    protected $fillable = [
        'nama',
        'sintaks',
        'deskripsi',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];
}
