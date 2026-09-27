<?php

namespace App\Imports;

use App\Models\JamMengajar;
use App\Models\TingkatKelas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

/**
 * Import Excel untuk menu Master Data > Jam Mengajar.
 *
 * Kolom yang dibaca (heading row, urutan bebas):
 * Jam Ke | Tingkat Kelas | Hari | Nama | Jam Mulai | Jam Selesai | Keterangan
 *
 * "Tingkat Kelas" dicocokkan ke App\Models\TingkatKelas (database datacenter)
 * berdasarkan nama, mis. "Kelas 7"; boleh dikosongkan bila jam berlaku untuk
 * semua tingkat. "Hari" juga boleh dikosongkan bila berlaku setiap hari.
 *
 * Baris dengan kombinasi Jam Ke + Tingkat Kelas + Hari yang sudah ada akan
 * ditimpa (update); kombinasi baru ditambahkan sebagai baris baru.
 */
class JamMengajarImport implements ToCollection, WithHeadingRow
{
    /** Hari yang boleh dipakai, mengikuti pilihan di form Jam Mengajar. */
    public const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    /** Jumlah baris yang berhasil disimpan. */
    public int $berhasil = 0;

    /** Daftar pesan baris yang gagal (nomor baris + alasan). */
    public array $gagal = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // +2: baris 1 adalah heading, index koleksi mulai dari 0.
            $baris = $index + 2;

            $jamKe = trim((string) ($row['jam_ke'] ?? ''));
            $namaTingkat = trim((string) ($row['tingkat_kelas'] ?? ''));
            $hari = trim((string) ($row['hari'] ?? ''));
            $nama = trim((string) ($row['nama'] ?? ''));
            $keterangan = trim((string) ($row['keterangan'] ?? ''));
            $mulaiAsli = $row['jam_mulai'] ?? '';
            $selesaiAsli = $row['jam_selesai'] ?? '';

            // Lewati baris yang benar-benar kosong (mis. sisa baris di file).
            if ($jamKe === '' && $namaTingkat === '' && $hari === '' && $nama === ''
                && trim((string) $mulaiAsli) === '' && trim((string) $selesaiAsli) === '') {
                continue;
            }

            if ($jamKe === '' || ! is_numeric($jamKe) || (int) $jamKe < 1) {
                $this->gagal[] = "Baris {$baris}: kolom Jam Ke harus berupa angka mulai dari 1.";

                continue;
            }

            $tingkatId = null;
            if ($namaTingkat !== '') {
                $tingkat = TingkatKelas::where('nama', $namaTingkat)->first();
                if (! $tingkat) {
                    $this->gagal[] = "Baris {$baris}: Tingkat Kelas \"{$namaTingkat}\" tidak ditemukan.";

                    continue;
                }
                $tingkatId = $tingkat->id;
            }

            if ($hari !== '') {
                $cocok = collect(self::HARI)->first(fn ($h) => strcasecmp($h, $hari) === 0);
                if (! $cocok) {
                    $this->gagal[] = "Baris {$baris}: Hari \"{$hari}\" tidak dikenali (pilihan: ".implode(', ', self::HARI).').';

                    continue;
                }
                $hari = $cocok;
            }

            $mulai = $this->jam($mulaiAsli);
            $selesai = $this->jam($selesaiAsli);

            if ($mulai === null || $selesai === null) {
                $this->gagal[] = "Baris {$baris}: Jam Mulai/Jam Selesai wajib diisi dengan format jam, mis. 07:00.";

                continue;
            }

            if ($selesai <= $mulai) {
                $this->gagal[] = "Baris {$baris}: Jam Selesai harus lebih besar dari Jam Mulai.";

                continue;
            }

            JamMengajar::updateOrCreate(
                [
                    'jam_ke' => (int) $jamKe,
                    'tingkat_kelas_id' => $tingkatId,
                    'hari' => $hari !== '' ? $hari : null,
                ],
                [
                    'nama' => $nama !== '' ? $nama : 'Jam ke-'.(int) $jamKe,
                    'jam_mulai' => $mulai,
                    'jam_selesai' => $selesai,
                    'keterangan' => $keterangan !== '' ? $keterangan : null,
                ]
            );

            $this->berhasil++;
        }
    }

    /**
     * Ubah isi sel jadi format "HH:MM:SS". Sel bertipe waktu di Excel terbaca
     * sebagai angka pecahan hari (mis. 0,2916 = 07:00), sedangkan sel teks
     * terbaca apa adanya ("07:00", "7.00", "07:00:00").
     */
    private function jam(mixed $nilai): ?string
    {
        if ($nilai === null || trim((string) $nilai) === '') {
            return null;
        }

        if (is_numeric($nilai)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $nilai)->format('H:i:s');
            } catch (Throwable) {
                return null;
            }
        }

        if (preg_match('/^(\d{1,2})[:.](\d{1,2})(?:[:.](\d{1,2}))?/', trim((string) $nilai), $c)) {
            $jam = (int) $c[1];
            $menit = (int) $c[2];
            $detik = (int) ($c[3] ?? 0);

            if ($jam > 23 || $menit > 59 || $detik > 59) {
                return null;
            }

            return sprintf('%02d:%02d:%02d', $jam, $menit, $detik);
        }

        return null;
    }
}
