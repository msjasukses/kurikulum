<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Jadwal magang/PKL siswa dari aplikasi Absensi (database "absensi",
 * tabel jadwal_magang) — read-only. Jadwal diatur di menu Jadwal Magang
 * aplikasi Absensi; aplikasi ini hanya mengikutinya untuk Jurnal PKL.
 *
 * Kolom `nis` merujuk siswa.nis di datacenter (fallback nisn), `hari`
 * berisi nomor hari ISO dipisah koma (1 = Senin .. 7 = Minggu).
 */
class JadwalMagang extends Model
{
    protected $connection = 'absensi';

    protected $table = 'jadwal_magang';

    public $timestamps = false;

    protected $guarded = ['*'];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public const NAMA_HARI = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

    /** Jadwal milik seorang siswa (dicocokkan lewat NIS, fallback NISN). */
    public function scopeMilik(Builder $query, Siswa $siswa): Builder
    {
        return $query->whereIn('nis', array_values(array_filter([$siswa->nis, $siswa->nisn])));
    }

    /** @return array<int, int> nomor hari ISO yang dijadwalkan magang */
    public function daftarHari(): array
    {
        return array_map('intval', array_filter(explode(',', (string) $this->hari)));
    }

    public function namaHari(): string
    {
        return implode(', ', array_map(fn ($h) => self::NAMA_HARI[$h] ?? $h, $this->daftarHari()));
    }

    /** Tanggal tsb masuk periode dan jatuh pada hari magang (sama dengan magangPada() di Absensi). */
    public function berlakuPada(CarbonInterface $tanggal): bool
    {
        return $tanggal->betweenIncluded($this->tanggal_mulai, $this->tanggal_selesai)
            && in_array($tanggal->dayOfWeekIso, $this->daftarHari(), true);
    }

    /**
     * Semua tanggal magang dalam jadwal ini sampai batas tertentu
     * (default hari ini), terbaru lebih dulu.
     *
     * @return array<int, Carbon>
     */
    public function tanggalMagang(?CarbonInterface $sampai = null): array
    {
        $akhir = Carbon::parse(min($this->tanggal_selesai, $sampai ?? today()));
        $out = [];

        for ($t = $akhir->copy(); $t->gte($this->tanggal_mulai); $t->subDay()) {
            if (in_array($t->dayOfWeekIso, $this->daftarHari(), true)) {
                $out[] = $t->copy();
            }
        }

        return $out;
    }
}
