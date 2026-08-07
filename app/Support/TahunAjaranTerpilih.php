<?php

namespace App\Support;

use App\Models\TahunAjaran;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Tahun ajaran yang sedang dipilih user lewat dropdown di topbar.
 *
 * Pilihan disimpan di session, jadi berlaku untuk seluruh halaman selama
 * user login. Semua data yang punya penanda tahun ajaran (kelas/rombel,
 * penugasan guru mapel, wali kelas, jadwal, agenda, alokasi jam, modul
 * ajar, pemetaan CP-TP-ATP, penempatan siswa) mengikuti pilihan ini.
 *
 * Bila user belum pernah memilih, yang dipakai adalah tahun ajaran dengan
 * is_aktif = true; kalau tidak ada juga, dipakai tahun ajaran terbaru.
 *
 * Dua bentuk penanda dipakai di aplikasi ini:
 *  - kolom "tahun_ajaran_id" (relasi ke tabel tahun_ajaran)  -> id()
 *  - kolom "tahun_ajaran" (teks nama, mis. "2026/2027")      -> nama()
 */
class TahunAjaranTerpilih
{
    public const SESSION_KEY = 'tahun_ajaran_terpilih';

    private ?Collection $daftar = null;

    private ?TahunAjaran $terpilih = null;

    private bool $sudahDitentukan = false;

    /** Semua tahun ajaran, terbaru di atas. */
    public function daftar(): Collection
    {
        if ($this->daftar !== null) {
            return $this->daftar;
        }

        try {
            return $this->daftar = TahunAjaran::orderByDesc('nama_tahun_ajaran')->get();
        } catch (Throwable) {
            // Database datacenter tidak bisa dihubungi — jangan sampai
            // seluruh halaman ikut gagal hanya karena dropdown ini.
            return $this->daftar = collect();
        }
    }

    public function terpilih(): ?TahunAjaran
    {
        if ($this->sudahDitentukan) {
            return $this->terpilih;
        }
        $this->sudahDitentukan = true;

        $daftar = $this->daftar();
        $id = $this->dariSession();

        return $this->terpilih = ($id ? $daftar->firstWhere('id', $id) : null)
            ?: $daftar->firstWhere('is_aktif', true)
            ?: $daftar->first();
    }

    /** Id tahun ajaran terpilih, untuk kolom "tahun_ajaran_id". */
    public function id(): ?int
    {
        return optional($this->terpilih())->id;
    }

    /** Nama tahun ajaran terpilih, untuk kolom teks "tahun_ajaran". */
    public function nama(): ?string
    {
        return optional($this->terpilih())->nama_tahun_ajaran;
    }

    /** Apakah yang terpilih sama dengan tahun ajaran yang berstatus aktif. */
    public function samaDenganAktif(): bool
    {
        return (bool) optional($this->terpilih())->is_aktif;
    }

    /**
     * Simpan pilihan user ke session. Mengembalikan false bila id tidak
     * dikenali, supaya controller bisa menolak input sembarangan.
     */
    public function pilih(int|string $id): bool
    {
        $ta = $this->daftar()->firstWhere('id', (int) $id);

        if (! $ta) {
            return false;
        }

        session([self::SESSION_KEY => $ta->id]);

        $this->terpilih = $ta;
        $this->sudahDitentukan = true;

        return true;
    }

    private function dariSession(): ?int
    {
        $request = request();

        if (! $request || ! $request->hasSession()) {
            return null;
        }

        $id = $request->session()->get(self::SESSION_KEY);

        return $id ? (int) $id : null;
    }
}
