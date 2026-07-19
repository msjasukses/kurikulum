<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamMengajar extends Model
{
    use HasFactory;

    protected $table = 'jam_mengajar';

    protected $fillable = [
        'jam_ke',
        'tingkat_kelas_id',
        'hari',
        'nama',
        'jam_mulai',
        'jam_selesai',
        'keterangan',
    ];

    public function tingkatKelas()
    {
        return $this->belongsTo(TingkatKelas::class);
    }

    /**
     * Label ringkas untuk dropdown pemilihan Jam Mengajar di menu lain
     * (mis. Jadwal Mengajar Guru), mis. "Jam ke-1 (07:00-07:45)".
     */
    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn () => trim(($this->nama ?: 'Jam ke-'.$this->jam_ke).' ('.substr((string) $this->jam_mulai, 0, 5).'-'.substr((string) $this->jam_selesai, 0, 5).')'),
        );
    }
}
