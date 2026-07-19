<?php

namespace App\Imports;

use App\Models\MataPelajaran;
use App\Models\PemetaanCpTpAtp;
use App\Models\TingkatKelas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import Excel untuk menu Pemetaan CP-TP-ATP.
 *
 * Kolom yang dibaca (heading row, urutan bebas):
 * Kode Mapel | Tingkat Kelas | Fase | Capaian Pembelajaran |
 * Tujuan Pembelajaran | Alur Tujuan Pembelajaran | Tahun Ajaran
 *
 * "Kode Mapel" dicocokkan ke App\Models\MataPelajaran (database datacenter)
 * dan "Tingkat Kelas" dicocokkan ke App\Models\TingkatKelas (database
 * datacenter) berdasarkan nama persis (case-sensitive di level SQL bawaan
 * MySQL collation utf8mb4_unicode_ci sudah case-insensitive).
 *
 * Baris dengan kombinasi Mata Pelajaran + Tingkat Kelas + Fase + Tahun
 * Ajaran yang sudah ada akan ditimpa (update); kombinasi baru akan
 * ditambahkan sebagai baris baru.
 */
class PemetaanCpTpAtpImport implements ToCollection, WithHeadingRow
{
    /** Jumlah baris yang berhasil disimpan. */
    public int $berhasil = 0;

    /** Daftar pesan baris yang gagal (nomor baris + alasan). */
    public array $gagal = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // +2: baris 1 adalah heading, index koleksi mulai dari 0.
            $baris = $index + 2;

            $kodeMapel = trim((string) ($row['kode_mapel'] ?? ''));
            $namaTingkat = trim((string) ($row['tingkat_kelas'] ?? ''));
            $fase = trim((string) ($row['fase'] ?? ''));
            $cp = trim((string) ($row['capaian_pembelajaran'] ?? ''));
            $tp = trim((string) ($row['tujuan_pembelajaran'] ?? ''));
            $atp = trim((string) ($row['alur_tujuan_pembelajaran'] ?? ''));
            $tahunAjaran = trim((string) ($row['tahun_ajaran'] ?? ''));

            // Lewati baris yang benar-benar kosong (mis. sisa baris kosong di file).
            if ($kodeMapel === '' && $namaTingkat === '' && $fase === '' && $cp === '' && $tp === '' && $atp === '') {
                continue;
            }

            if ($kodeMapel === '') {
                $this->gagal[] = "Baris {$baris}: kolom Kode Mapel kosong.";

                continue;
            }

            $mapel = MataPelajaran::where('kode_mapel', $kodeMapel)->first();
            if (! $mapel) {
                $this->gagal[] = "Baris {$baris}: Kode Mapel \"{$kodeMapel}\" tidak ditemukan.";

                continue;
            }

            if ($namaTingkat === '') {
                $this->gagal[] = "Baris {$baris}: kolom Tingkat Kelas kosong.";

                continue;
            }

            $tingkat = TingkatKelas::where('nama', $namaTingkat)->first();
            if (! $tingkat) {
                $this->gagal[] = "Baris {$baris}: Tingkat Kelas \"{$namaTingkat}\" tidak ditemukan.";

                continue;
            }

            if ($fase === '') {
                $this->gagal[] = "Baris {$baris}: kolom Fase kosong.";

                continue;
            }

            if ($cp === '' || $tp === '' || $atp === '') {
                $this->gagal[] = "Baris {$baris}: Capaian/Tujuan/Alur Tujuan Pembelajaran tidak boleh kosong.";

                continue;
            }

            PemetaanCpTpAtp::updateOrCreate(
                [
                    'mata_pelajaran_id' => $mapel->id,
                    'tingkat_kelas_id' => $tingkat->id,
                    'fase' => $fase,
                    'tahun_ajaran' => $tahunAjaran !== '' ? $tahunAjaran : null,
                ],
                [
                    'capaian_pembelajaran' => $cp,
                    'tujuan_pembelajaran' => $tp,
                    'alur_tujuan_pembelajaran' => $atp,
                ]
            );

            $this->berhasil++;
        }
    }
}
