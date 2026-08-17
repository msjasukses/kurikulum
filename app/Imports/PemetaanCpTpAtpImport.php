<?php

namespace App\Imports;

use App\Models\MataPelajaran;
use App\Models\PemetaanCpTpAtp;
use App\Models\TingkatKelas;
use App\Support\TahunAjaranTerpilih;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import Excel untuk menu Pemetaan CP-TP-ATP.
 *
 * Kolom yang dibaca (heading row, urutan bebas):
 * Kode Mapel | Tingkat Kelas | Fase | Semester | Elemen |
 * Capaian Pembelajaran | Tujuan Pembelajaran | Alur Tujuan Pembelajaran |
 * Indikator KKTP | Model Pembelajaran | Sumber Belajar | Karakter DPL |
 * Tahun Ajaran
 *
 * Kolom Model Pembelajaran, Sumber Belajar, dan Karakter DPL boleh diisi
 * lebih dari satu pilihan, dipisahkan koma atau titik koma — di aplikasi
 * isinya tampil sebagai centang (checkbox).
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
            $semester = trim((string) ($row['semester'] ?? ''));
            $elemen = trim((string) ($row['elemen'] ?? ''));
            $kktp = trim((string) ($row['indikator_kktp'] ?? ''));
            $model = $this->pecahPilihan($row['model_pembelajaran'] ?? '');
            $sumber = $this->pecahPilihan($row['sumber_belajar'] ?? '');
            $karakter = $this->pecahPilihan($row['karakter_dpl'] ?? '');
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
                    // Kolom tahun ajaran boleh dikosongkan di file Excel —
                    // isinya mengikuti tahun ajaran yang dipilih di topbar.
                    'tahun_ajaran' => $tahunAjaran !== '' ? $tahunAjaran : app(TahunAjaranTerpilih::class)->nama(),
                ],
                [
                    'semester' => $semester !== '' ? $semester : null,
                    'elemen' => $elemen !== '' ? $elemen : null,
                    'capaian_pembelajaran' => $cp,
                    'tujuan_pembelajaran' => $tp,
                    'alur_tujuan_pembelajaran' => $atp,
                    'indikator_kktp' => $kktp !== '' ? $kktp : null,
                    'model_pembelajaran' => $model,
                    'sumber_belajar' => $sumber,
                    'karakter_dpl' => $karakter,
                ]
            );

            $this->berhasil++;
        }
    }

    /** Ubah isi sel "A, B; C" menjadi array ['A', 'B', 'C']. */
    private function pecahPilihan($nilai): array
    {
        $bagian = preg_split('/[,;\r\n]+/', (string) $nilai) ?: [];

        return array_values(array_filter(array_map('trim', $bagian), fn ($v) => $v !== ''));
    }
}
