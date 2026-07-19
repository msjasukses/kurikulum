<?php

namespace App\Http\Controllers\Concerns;

use App\Models\GuruMapel;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\TingkatKelas;
use Illuminate\Support\Collection;

/**
 * Pembatas pilihan mapel/kelas untuk guru yang sedang login: mengikuti
 * penugasan di menu "Data Guru Mata Pelajaran" (tabel guru_mapel database
 * datacenter) pada tahun ajaran aktif. Admin tidak dibatasi (return null).
 */
trait FilterPenugasanGuru
{
    private ?Collection $penugasanGuru = null;
    private bool $penugasanGuruDimuat = false;

    /** Baris guru_mapel milik guru login; null bila bukan guru datacenter. */
    protected function penugasanGuru(): ?Collection
    {
        if ($this->penugasanGuruDimuat) {
            return $this->penugasanGuru;
        }
        $this->penugasanGuruDimuat = true;

        $user = auth()->user();
        if (! $user || ! $user->isGuru() || ! $user->guru_id) {
            return $this->penugasanGuru = null;
        }

        $ta = TahunAjaran::where('is_aktif', true)->first();

        return $this->penugasanGuru = GuruMapel::where('guru_id', $user->guru_id)
            ->when($ta, fn ($q) => $q->where('tahun_ajaran_id', $ta->id))
            ->get(['mata_pelajaran_id', 'rombongan_belajar_id']);
    }

    /** Id mapel yang diampu guru login; null = tidak dibatasi (admin). */
    protected function mapelIdsGuru(): ?array
    {
        $p = $this->penugasanGuru();

        return $p?->pluck('mata_pelajaran_id')->filter()->unique()->values()->all();
    }

    /** Id kelas/rombel yang diampu guru login; null = tidak dibatasi. */
    protected function kelasIdsGuru(): ?array
    {
        $p = $this->penugasanGuru();

        return $p?->pluck('rombongan_belajar_id')->filter()->unique()->values()->all();
    }

    /** Id tingkat_kelas dari kelas yang diampu; null = tidak dibatasi. */
    protected function tingkatIdsGuru(): ?array
    {
        $kelasIds = $this->kelasIdsGuru();
        if ($kelasIds === null) {
            return null;
        }

        $nomorTingkat = Kelas::whereIn('id', $kelasIds)->pluck('tingkat')->unique();

        return TingkatKelas::whereIn('nomor', $nomorTingkat)->pluck('id')->all();
    }

    /** Bungkus id jadi ['ids' => ...] untuk relasi field BaseCrud; [] bila tak dibatasi. */
    protected function idsFilter(?array $ids): array
    {
        return $ids === null ? [] : ['ids' => $ids];
    }
}
