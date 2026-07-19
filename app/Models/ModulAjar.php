<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulAjar extends Model
{
    use HasFactory;

    protected $table = 'modul_ajar';

    /** Bagian-bagian isi modul yang dikelola Generator Modul Ajar. */
    public const BAGIAN_ISI = [
        'kegiatan_pembelajaran' => 'Kegiatan Pembelajaran',
        'target_peserta_didik' => 'Target Peserta Didik',
        'model_pembelajaran' => 'Model Pembelajaran',
        'pertanyaan_pemantik' => 'Pertanyaan Pemantik',
        'pendahuluan' => 'Pendahuluan',
        'kegiatan_inti' => 'Kegiatan Inti',
        'penutup' => 'Penutup',
        'asesmen_diagnostik' => 'Asesmen Diagnostik',
        'asesmen_formatif' => 'Asesmen Formatif',
        'asesmen_sumatif' => 'Asesmen Sumatif',
    ];

    /** Bagian-bagian LKPD (Lembar Kerja Peserta Didik). */
    public const BAGIAN_LKPD = [
        'lkpd_tujuan' => 'Tujuan',
        'lkpd_petunjuk' => 'Petunjuk',
        'lkpd_kegiatan' => 'Kegiatan',
        'lkpd_refleksi' => 'Refleksi',
        'lkpd_asesmen' => 'Asesmen',
    ];

    protected $fillable = [
        'judul',
        'mata_pelajaran_id',
        'tingkat_kelas_id',
        'pegawai_id',
        'fase',
        'file',
        'deskripsi',
        'tahun_ajaran',
        'jumlah_jam',
        'pertemuan_ke',
        'semester',
        'pemetaan_cp_tp_atp_id',
        'capaian_pembelajaran',
        'tujuan_pembelajaran',
        'alur_tujuan_pembelajaran',
        'kegiatan_pembelajaran',
        'target_peserta_didik',
        'model_pembelajaran',
        'pertanyaan_pemantik',
        'pendahuluan',
        'kegiatan_inti',
        'penutup',
        'asesmen_diagnostik',
        'asesmen_formatif',
        'asesmen_sumatif',
        'lkpd_tujuan',
        'lkpd_petunjuk',
        'lkpd_kegiatan',
        'lkpd_refleksi',
        'lkpd_asesmen',
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function tingkatKelas()
    {
        return $this->belongsTo(TingkatKelas::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function pemetaan()
    {
        return $this->belongsTo(PemetaanCpTpAtp::class, 'pemetaan_cp_tp_atp_id');
    }
}
