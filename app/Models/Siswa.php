<?php

namespace App\Models;

use App\Support\TahunAjaranTerpilih;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    /**
     * Data Siswa diambil dari database "datacenter" (read-only), bukan
     * dari database lokal "kurikulum". Lihat config/database.php.
     *
     * Kolom login/keamanan (password, remember_token, otp_*, session, dst.)
     * sengaja tidak dimasukkan ke $fillable karena menu ini read-only.
     */
    protected $connection = 'datacenter';

    protected $table = 'siswa';

    protected $fillable = [
        'nisn',
        'nis',
        'nama_siswa',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'alamat',
        'nomor_hp',
        'email',
        'nama_ayah',
        'nama_ibu',
        'nomor_hp_ortu',
        'foto',
        'is_aktif',
        'status_siswa',
        'tanggal_status',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_status' => 'date',
        'is_aktif' => 'boolean',
    ];

    /**
     * Penempatan kelas siswa pada tahun ajaran yang sedang dipilih di
     * topbar (satu baris siswa_rombel per tahun ajaran). Bila daftar tahun
     * ajaran tidak tersedia, dipakai penempatan dengan tahun_ajaran_id
     * terbesar — yaitu yang paling mutakhir.
     */
    public function rombelSaatIni()
    {
        $tahunAjaranId = app(TahunAjaranTerpilih::class)->id();

        if ($tahunAjaranId) {
            return $this->hasOne(SiswaRombel::class, 'siswa_id')
                ->where('tahun_ajaran_id', $tahunAjaranId);
        }

        return $this->hasOne(SiswaRombel::class, 'siswa_id')->latestOfMany('tahun_ajaran_id');
    }
}
