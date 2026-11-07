<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Satu buku pada katalog buku digital, hasil sinkron dari katalog SIBI
 * (Sistem Informasi Perbukuan Indonesia) milik Kemendikdasmen.
 *
 * Berkas PDF dan gambar cover bisa diunduh ke disk `public` supaya katalog
 * tetap terpakai saat server SIBI sedang tidak bisa diakses.
 */
class BukuDigital extends Model
{
    use HasFactory;

    protected $table = 'buku_digital';

    protected $fillable = [
        'sibi_id',
        'slug',
        'sumber_feed',
        'judul',
        'penulis',
        'editor',
        'ilustrator',
        'penerbit',
        'isbn',
        'bahasa',
        'jenjang',
        'tingkat_kelas',
        'mata_pelajaran',
        'kategori',
        'jenis_buku',
        'kurikulum',
        'tipe',
        'edisi',
        'format',
        'tahun_terbit',
        'deskripsi',
        'url_cover',
        'url_file',
        'cover_path',
        'file_path',
        'ukuran_file',
        'diunduh_pada',
        'gagal_unduh',
        'disinkron_pada',
    ];

    protected $casts = [
        'diunduh_pada' => 'datetime',
        'disinkron_pada' => 'datetime',
        'ukuran_file' => 'integer',
    ];

    /** Bab utama buku beserta sub babnya, urut sesuai urutan di buku. */
    public function bab()
    {
        return $this->hasMany(BukuDigitalBab::class, 'buku_digital_id')
            ->where('level', 1)
            ->orderBy('urutan');
    }

    /** Seluruh baris daftar isi (bab dan sub bab) tanpa penjenjangan. */
    public function semuaBab()
    {
        return $this->hasMany(BukuDigitalBab::class, 'buku_digital_id')->orderBy('urutan');
    }

    /** Berkas bukunya sudah tersimpan di penyimpanan lokal aplikasi. */
    public function sudahDiunduh(): bool
    {
        return filled($this->file_path) && Storage::disk('public')->exists($this->file_path);
    }

    /** Hanya buku PDF yang berkasnya bisa diunduh ke lokal. */
    public function bisaDiunduh(): bool
    {
        return $this->tipe === 'pdf' && filled($this->url_file);
    }

    /**
     * Tautan untuk membuka buku: utamakan berkas lokal, jatuh ke tautan SIBI
     * bila belum pernah diunduh.
     */
    public function urlBaca(): ?string
    {
        if ($this->sudahDiunduh()) {
            return Storage::url($this->file_path);
        }

        return $this->url_file ?: null;
    }

    /** Tautan cover: berkas lokal bila ada, selain itu tautan asli SIBI. */
    public function urlCover(): ?string
    {
        if (filled($this->cover_path) && Storage::disk('public')->exists($this->cover_path)) {
            return Storage::url($this->cover_path);
        }

        return $this->url_cover ?: null;
    }

    /** Ukuran berkas dalam satuan yang enak dibaca, mis. "12,4 MB". */
    public function ukuranFileManusiawi(): ?string
    {
        if (! $this->ukuran_file) {
            return null;
        }

        $mb = $this->ukuran_file / 1048576;

        return $mb < 1
            ? number_format($this->ukuran_file / 1024, 0, ',', '.').' KB'
            : number_format($mb, 1, ',', '.').' MB';
    }

    /** Label tipe buku beserta warna badge-nya untuk tampilan katalog. */
    public function penandaTipe(): array
    {
        return match ($this->tipe) {
            'audio' => ['teks' => 'Audio', 'kelas' => 'bg-info text-dark', 'icon' => 'bi-headphones'],
            'interactive' => ['teks' => 'Interaktif', 'kelas' => 'bg-warning text-dark', 'icon' => 'bi-joystick'],
            default => ['teks' => 'PDF', 'kelas' => 'bg-danger', 'icon' => 'bi-file-earmark-pdf'],
        };
    }

    /** Pencarian bebas pada judul, penulis, penerbit, dan ISBN. */
    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        if (blank($kata)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($kata) {
            foreach (['judul', 'penulis', 'penerbit', 'isbn'] as $kolom) {
                $q->orWhere($kolom, 'like', '%'.$kata.'%');
            }
        });
    }
}
