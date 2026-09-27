<?php

namespace App\Services;

use App\Models\ModulAjar;
use App\Models\User;
use App\Services\Ai\PengaturanAi;
use RuntimeException;

/**
 * Penyusun isi Modul Ajar & LKPD dengan bantuan AI.
 *
 * Penyedia (Anthropic/Groq/Gemini), kunci API, dan modelnya mengikuti
 * pengaturan milik akun yang sedang login — lihat App\Services\Ai\PengaturanAi.
 * Bila akun belum mengisi kunci dan aplikasi juga tidak punya kunci cadangan
 * di .env, halaman modul ajar memakai kerangka bawaan aplikasi.
 */
class GeneratorModulAjarAi
{
    /** Bagian yang diminta ke AI: isi modul (tanpa kegiatan_pembelajaran) + LKPD. */
    public static function bagian(): array
    {
        $bagian = ModulAjar::BAGIAN_ISI + ModulAjar::BAGIAN_LKPD;
        unset($bagian['kegiatan_pembelajaran']);

        return $bagian;
    }

    public function pengaturan(?User $user): ?PengaturanAi
    {
        return PengaturanAi::untuk($user);
    }

    public function tersedia(?User $user): bool
    {
        return $this->pengaturan($user) !== null;
    }

    /**
     * Susun isi modul dari konteks pembelajaran.
     *
     * @param  array  $konteks  mapel, tingkat, fase, semester, jumlah_jam,
     *                          pertemuan_ke, judul, cp, tp, atp
     * @return array<string, string> isi per bagian dalam bentuk HTML sederhana
     */
    public function generate(array $konteks, ?User $user): array
    {
        $pengaturan = $this->pengaturan($user);

        if (! $pengaturan) {
            throw new RuntimeException('Kunci API AI belum diatur.');
        }

        $jawaban = $pengaturan->buatPenyedia()->kirim(
            $pengaturan->apiKey,
            $pengaturan->model,
            $this->instruksi(),
            $this->permintaan($konteks),
            $this->skema(),
        );

        return $this->bacaHasil($jawaban, $pengaturan->label());
    }

    /** Semua bagian wajib diisi supaya tidak ada kolom yang kosong di form. */
    private function skema(): array
    {
        $properties = [];
        foreach (self::bagian() as $name => $label) {
            $properties[$name] = [
                'type' => 'string',
                'description' => $label.' — HTML sederhana.',
            ];
        }

        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => array_keys($properties),
            'additionalProperties' => false,
        ];
    }

    private function instruksi(): string
    {
        return <<<'TEKS'
        Anda adalah guru senior sekaligus pengembang kurikulum di sekolah Indonesia
        yang menyusun Modul Ajar dan LKPD sesuai Kurikulum Merdeka.

        Ketentuan penulisan:
        - Tulis seluruh isi dalam Bahasa Indonesia yang baku, jelas, dan siap pakai
          oleh guru tanpa perlu banyak penyuntingan.
        - Isi setiap bagian harus benar-benar spesifik pada materi, mata pelajaran,
          dan jenjang kelas yang diberikan. Jangan menulis kalimat umum yang bisa
          dipakai untuk materi apa pun, dan jangan memakai tanda kurung isian
          seperti "(sebutkan ...)".
        - Gunakan HTML sederhana: <p> untuk paragraf, <ol>/<ul> dengan <li> untuk
          daftar langkah, serta <strong> bila perlu penegasan. Jangan memakai
          heading, tabel, gaya inline, atau blok kode.
        - Kegiatan pembelajaran (pendahuluan, kegiatan inti, penutup) ditulis
          sebagai langkah bernomor yang runtut dan masuk akal untuk alokasi waktu
          yang tersedia.
        - Bagian asesmen harus menyebutkan bentuk/teknik penilaian yang konkret.
        - Bagian LKPD ditujukan langsung kepada peserta didik, memakai kalimat
          perintah yang mudah dipahami sesuai usia mereka.
        TEKS;
    }

    private function permintaan(array $konteks): string
    {
        $baris = [
            'Mata Pelajaran' => $konteks['mapel'] ?? null,
            'Kelas/Tingkat' => $konteks['tingkat'] ?? null,
            'Fase' => $konteks['fase'] ?? null,
            'Semester' => $konteks['semester'] ?? null,
            'Alokasi Waktu' => isset($konteks['jumlah_jam']) ? $konteks['jumlah_jam'].' JP' : null,
            'Pertemuan Ke' => $konteks['pertemuan_ke'] ?? null,
            'Judul Materi' => $konteks['judul'] ?? null,
            'Capaian Pembelajaran (CP)' => $konteks['cp'] ?? null,
            'Tujuan Pembelajaran (TP)' => $konteks['tp'] ?? null,
            'Alur Tujuan Pembelajaran (ATP)' => $konteks['atp'] ?? null,
        ];

        $teks = "Susun isi Modul Ajar dan LKPD untuk pembelajaran berikut.\n\n";
        foreach (array_filter($baris, fn ($v) => filled($v)) as $label => $nilai) {
            $teks .= "{$label}: {$nilai}\n";
        }

        $teks .= "\nBagian yang harus diisi:\n";
        foreach (self::bagian() as $name => $label) {
            $teks .= "- {$name}: {$label}\n";
        }

        return $teks;
    }

    /** Ambil objek JSON dari jawaban, lalu saring ke bagian yang dikenal. */
    private function bacaHasil(string $jawaban, string $namaPenyedia): array
    {
        $hasil = json_decode($jawaban, true);

        // Sebagian model membungkus JSON dengan teks pembuka atau blok kode.
        if (! is_array($hasil) && preg_match('/\{.*\}/s', $jawaban, $c)) {
            $hasil = json_decode($c[0], true);
        }

        if (! is_array($hasil)) {
            throw new RuntimeException('Jawaban '.$namaPenyedia.' tidak bisa dibaca. Coba ulangi.');
        }

        $isi = [];
        foreach (array_keys(self::bagian()) as $name) {
            $nilai = trim((string) ($hasil[$name] ?? ''));
            if ($nilai !== '') {
                $isi[$name] = $nilai;
            }
        }

        if (empty($isi)) {
            throw new RuntimeException($namaPenyedia.' tidak mengembalikan isi modul. Coba ulangi.');
        }

        return $isi;
    }
}
