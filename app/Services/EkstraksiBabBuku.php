<?php

namespace App\Services;

use App\Models\BukuDigital;
use App\Models\BukuDigitalBab;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Mengambil daftar bab, sub bab, dan topik materi sebuah buku digital.
 *
 * Dua sumber dipakai berdampingan:
 *  1. API SIBI (getDetails) — judul bab terstruktur dan tepercaya, lengkap
 *     dengan kata kunci. Hanya tersedia untuk sebagian buku (umumnya Buku
 *     Sekolah Penggerak) dan tidak pernah memuat sub bab.
 *  2. Daftar Isi di berkas PDF — dibaca dengan pdftotext. Satu-satunya sumber
 *     sub bab, dan jadi cadangan judul bab bila API tidak punya datanya.
 *     Hasilnya perkiraan, jadi ditandai sumber "pdf" dan disaring ketat.
 */
class EkstraksiBabBuku
{
    private const API_DETAIL = 'https://api.buku.kemendikdasmen.go.id/api/catalogue/getDetails?slug=';

    /** Nama bagian baku di buku SIBI yang bukan judul bab. */
    private const BUKAN_JUDUL = [
        'tujuan pembelajaran', 'kata kunci', 'peta materi', 'ayo bersiap', 'materi dasar',
        'latihan terpadu', 'tantangan terakhir', 'kembangkan potensimu', 'pojok munhwa',
        'refleksi', 'kosakata baru', 'uji kompetensi', 'daftar isi', 'daftar gambar',
        'daftar tabel', 'daftar pustaka', 'glosarium', 'indeks', 'kata pengantar',
        'prakata', 'petunjuk penggunaan', 'profil pelaku perbukuan', 'daftar sumber gambar',
        'pengenalan tokoh', 'ayo belajar sambil bermain', 'pendahuluan', 'penutup',
    ];

    /**
     * Sebagian PDF buku tidak memetakan ligatur "fi"/"fl" ke huruf aslinya,
     * sehingga pdftotext menghasilkan "Arti sial" (ligatur jadi spasi) atau
     * "Releksi" (ligatur hilang sama sekali). Hurufnya benar-benar tidak ada
     * di berkas, jadi hanya kata yang sudah pasti yang dipulihkan.
     */
    private const PERBAIKAN_LIGATUR = [
        // Ligatur berubah menjadi spasi.
        'Arti sial' => 'Artifisial',
        'arti sial' => 'artifisial',
        'Klasi kasi' => 'Klasifikasi',
        'klasi kasi' => 'klasifikasi',
        'Identi kasi' => 'Identifikasi',
        'identi kasi' => 'identifikasi',
        'De nisi' => 'Definisi',
        'de nisi' => 'definisi',
        // Ligatur hilang tanpa sisa.
        'Releksi' => 'Refleksi',
        'releksi' => 'refleksi',
        'Konlik' => 'Konflik',
        'konlik' => 'konflik',
        'Graik' => 'Grafik',
        'graik' => 'grafik',
        'Tipograi' => 'Tipografi',
        'tipograi' => 'tipografi',
        'Spesiik' => 'Spesifik',
        'spesiik' => 'spesifik',
        'Eisien' => 'Efisien',
        'eisien' => 'efisien',
    ];

    /**
     * Kata sambung dan kata depan yang tetap huruf kecil pada judul berbahasa
     * Indonesia, dipakai saat merapikan judul yang ditulis KAPITAL SEMUA.
     */
    private const KATA_KECIL = [
        'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'pada', 'dengan', 'yang',
        'dalam', 'bagi', 'serta', 'tentang', 'oleh', 'terhadap', 'sebagai',
    ];

    /**
     * Pembungkus aman untuk dipanggil tepat setelah buku diunduh: kegagalan
     * mengambil bab tidak boleh membatalkan unduhan yang sudah berhasil.
     *
     * @return array{bab:int, sub:int, sumber:string, pesan:string}
     */
    public function untukBukuDenganAman(BukuDigital $buku, string $mode = 'auto'): array
    {
        try {
            return $this->untukBuku($buku, $mode);
        } catch (Throwable $e) {
            report($e);

            return [
                'bab' => 0,
                'sub' => 0,
                'sumber' => '-',
                'pesan' => 'Daftar bab belum bisa diambil, jalankan "php artisan buku:bab" nanti.',
            ];
        }
    }

    /**
     * Mengisi daftar bab & sub bab sebuah buku lalu menyimpannya.
     *
     * @return array{bab:int, sub:int, sumber:string, pesan:string}
     */
    public function untukBuku(BukuDigital $buku, string $mode = 'auto'): array
    {
        $babApi = ($mode === 'auto' || $mode === 'api') ? $this->dariApi($buku) : [];
        $babPdf = ($mode === 'auto' || $mode === 'pdf') ? $this->dariPdf($buku) : [];

        if ($babApi) {
            // Judul bab dari API dipertahankan karena lebih tepercaya, lalu
            // diperkaya sub bab hasil baca PDF yang nomor babnya cocok.
            $struktur = $this->gabungkan($babApi, $babPdf);
            $sumber = 'api';
        } elseif ($babPdf) {
            $struktur = $babPdf;
            $sumber = 'pdf';
        } else {
            return ['bab' => 0, 'sub' => 0, 'sumber' => '-', 'pesan' => 'Daftar bab tidak ditemukan.'];
        }

        $jumlahSub = array_sum(array_map(fn ($b) => count($b['sub'] ?? []), $struktur));
        $this->simpan($buku, $struktur, $sumber);

        $pesan = count($struktur).' bab';
        $pesan .= $jumlahSub > 0 ? " + {$jumlahSub} sub bab" : ' (tanpa sub bab)';
        $pesan .= ' tersimpan (sumber: '.$sumber.($jumlahSub > 0 && $sumber === 'api' ? '+pdf' : '').').';

        return ['bab' => count($struktur), 'sub' => $jumlahSub, 'sumber' => $sumber, 'pesan' => $pesan];
    }

    /**
     * Menempelkan sub bab hasil baca PDF ke daftar bab dari API.
     * Pencocokan lewat nomor bab yang dinormalkan (angka maupun romawi).
     */
    private function gabungkan(array $babApi, array $babPdf): array
    {
        $subPerNomor = [];
        foreach ($babPdf as $b) {
            $n = $this->nomorKeAngka($b['nomor']);
            if ($n !== null && ! empty($b['sub'])) {
                $subPerNomor[$n] = $b['sub'];
            }
        }

        foreach ($babApi as $i => $b) {
            $n = $this->nomorKeAngka($b['nomor']);
            // Bila nomor bab tidak terbaca, pakai urutan sebagai cadangan.
            $babApi[$i]['sub'] = $subPerNomor[$n] ?? $subPerNomor[$i + 1] ?? [];
        }

        return $babApi;
    }

    /** "IV" dan "4" sama-sama menjadi 4, supaya bisa dicocokkan. */
    private function nomorKeAngka(?string $nomor): ?int
    {
        if (blank($nomor)) {
            return null;
        }

        $nomor = trim($nomor);
        if (ctype_digit($nomor)) {
            return (int) $nomor;
        }

        $peta = ['I' => 1, 'V' => 5, 'X' => 10, 'L' => 50, 'C' => 100];
        $romawi = strtoupper($nomor);
        if (! preg_match('/^[IVXLC]+$/', $romawi)) {
            return null;
        }

        $hasil = 0;
        $panjang = strlen($romawi);
        for ($i = 0; $i < $panjang; $i++) {
            $kini = $peta[$romawi[$i]];
            $lanjut = $i + 1 < $panjang ? $peta[$romawi[$i + 1]] : 0;
            $hasil += $kini < $lanjut ? -$kini : $kini;
        }

        return $hasil ?: null;
    }

    /** Menulis ulang daftar bab & sub bab sebuah buku dalam satu transaksi. */
    private function simpan(BukuDigital $buku, array $struktur, string $sumber): void
    {
        DB::transaction(function () use ($buku, $struktur, $sumber) {
            // Ditulis ulang agar ekstraksi berulang tidak menumpuk. Sub bab
            // ikut terhapus lewat cascade pada induk_id.
            BukuDigitalBab::where('buku_digital_id', $buku->id)->delete();

            $urutan = 0;

            foreach ($struktur as $b) {
                $induk = BukuDigitalBab::create([
                    'buku_digital_id' => $buku->id,
                    'induk_id' => null,
                    'level' => 1,
                    'nomor_bab' => $this->potong($b['nomor'] ?? null, 20),
                    'judul_bab' => Str::limit($b['judul'], 299, ''),
                    'kata_kunci' => $b['kata_kunci'] ?? null,
                    'halaman' => $b['halaman'] ?? null,
                    'urutan' => ++$urutan,
                    'sumber' => $sumber,
                ]);

                foreach ($b['sub'] ?? [] as $s) {
                    BukuDigitalBab::create([
                        'buku_digital_id' => $buku->id,
                        'induk_id' => $induk->id,
                        'level' => 2,
                        'nomor_bab' => $this->potong($s['nomor'] ?? null, 20),
                        'judul_bab' => Str::limit($s['judul'], 299, ''),
                        'kata_kunci' => null,
                        'halaman' => $s['halaman'] ?? null,
                        'urutan' => ++$urutan,
                        // Sub bab selalu hasil baca PDF, apa pun sumber babnya.
                        'sumber' => 'pdf',
                    ]);
                }
            }
        });
    }

    private function potong(?string $nilai, int $panjang): ?string
    {
        return blank($nilai) ? null : Str::limit(trim($nilai), $panjang - 1, '');
    }

    /**
     * Mengambil bab dari API katalog SIBI.
     *
     * @return array<int, array{nomor:?string, judul:string, kata_kunci:?string, sub:array}>
     */
    public function dariApi(BukuDigital $buku): array
    {
        if (blank($buku->slug)) {
            return [];
        }

        try {
            $respons = Http::timeout(60)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 Chrome/131.0'])
                ->get(self::API_DETAIL.rawurlencode($buku->slug));
        } catch (Throwable) {
            return [];
        }

        if (! $respons->successful()) {
            return [];
        }

        $hasil = $respons->json('results');
        if (isset($hasil[0]) && is_array($hasil[0])) {
            $hasil = $hasil[0];
        }

        $bab = [];
        foreach ($hasil['chapters'] ?? [] as $c) {
            $judul = trim((string) ($c['chapter_title'] ?? ''));
            if ($judul === '') {
                continue;
            }

            $bab[] = [
                'nomor' => blank($c['chapter_number'] ?? null) ? null : trim((string) $c['chapter_number']),
                'judul' => preg_replace('/\s+/u', ' ', $judul),
                'kata_kunci' => blank($c['chapter_keywords'] ?? null) ? null : trim((string) $c['chapter_keywords']),
                'halaman' => null,
                'sub' => [],
            ];
        }

        return $bab;
    }

    /**
     * Membaca Daftar Isi dari berkas PDF yang sudah diunduh.
     *
     * @return array<int, array{nomor:?string, judul:string, halaman:?int, sub:array}>
     */
    public function dariPdf(BukuDigital $buku): array
    {
        if (! $buku->sudahDiunduh()) {
            return [];
        }

        $alat = $this->pdftotext();
        if ($alat === null) {
            return [];
        }

        $keluaran = tempnam(sys_get_temp_dir(), 'sibi_bab_').'.txt';

        // Daftar Isi hampir selalu berada di 25 halaman pertama; membatasi
        // halaman membuat prosesnya cepat walau berkasnya puluhan MB.
        exec(sprintf(
            '"%s" -f 1 -l 25 -layout -enc UTF-8 "%s" "%s" 2>&1',
            $alat,
            Storage::disk('public')->path($buku->file_path),
            $keluaran
        ), $abaikan, $kode);

        $teks = is_file($keluaran) ? (string) file_get_contents($keluaran) : '';
        @unlink($keluaran);

        if ($kode !== 0 || $teks === '') {
            return [];
        }

        return $this->uraiDaftarIsi($teks);
    }

    /** Mengurai blok Daftar Isi menjadi daftar bab berikut sub babnya. */
    private function uraiDaftarIsi(string $teks): array
    {
        // Byte non-UTF-8 harus dibuang, kalau tidak regex modifier /u gagal.
        $teks = mb_convert_encoding($teks, 'UTF-8', 'UTF-8');
        $baris = $this->potongWilayahDaftarIsi(preg_split('/\R/u', $teks) ?: []);
        $jml = count($baris);

        $bab = [];
        $kini = null;   // kunci bab yang sedang dikumpulkan sub babnya

        for ($i = 0; $i < $jml; $i++) {
            $mentah = $baris[$i];
            $b = trim($mentah);

            $nomor = null;
            $sisa = '';

            if (preg_match('/^Bab\s+([0-9]{1,2}|[IVX]{1,5})\s*$/iu', $b, $m)) {
                $nomor = $m[1];
            } elseif (preg_match('/^Bab\s+([0-9]{1,2}|[IVX]{1,5})\s*[:.\-]?\s+(.{3,})$/iu', $b, $m)) {
                $nomor = $m[1];
                $sisa = $m[2];
            }

            if ($nomor !== null) {
                $pecah = $this->pisahJudulHalaman($sisa);
                $judul = $pecah['judul'];
                $halaman = $pecah['halaman'];

                if ($this->judulMasukAkal($judul)) {
                    if (! $this->adaTitikPemandu($sisa)) {
                        $judul = $this->sambungJudul($judul, $baris, $i, $jml);
                    }
                } else {
                    $lanjut = $this->judulDariBarisBerikutnya($baris, $i, $jml);
                    $judul = $lanjut['judul'];
                    $halaman ??= $lanjut['halaman'];
                }

                if (! $this->judulMasukAkal($judul)) {
                    $kini = null;

                    continue;
                }

                $kunci = mb_strtoupper($nomor);
                // Nomor bab bisa muncul berulang (header halaman); simpan yang
                // judulnya terpanjang, tetapi jangan buang sub bab yang sudah ada.
                if (! isset($bab[$kunci]) || mb_strlen($judul) > mb_strlen($bab[$kunci]['judul'])) {
                    $bab[$kunci] = [
                        'nomor' => $nomor,
                        'judul' => $judul,
                        'halaman' => $halaman,
                        'sub' => $bab[$kunci]['sub'] ?? [],
                    ];
                }
                $kini = $kunci;

                continue;
            }

            // Sub bab hanya dikumpulkan sesudah ada bab induk, sehingga bagian
            // "Panduan Umum" sebelum Bab 1 tidak ikut tertarik.
            if ($kini !== null && isset($bab[$kini])) {
                $sub = $this->bacaSubBab($mentah);
                if ($sub !== null && ! $this->subSudahAda($bab[$kini]['sub'], $sub)) {
                    $bab[$kini]['sub'][] = $sub;
                }
            }
        }

        return $this->lolosPenjagaMutu($bab) ? array_values($bab) : [];
    }

    /**
     * Membaca satu baris sub bab. Dua penomoran yang dipakai buku SIBI:
     *  - huruf  : "A. Pendahuluan ..... 12"      (umum di Buku Sekolah Penggerak)
     *  - numerik: "1.1 Bilangan Berpangkat ... 4" (umum di Buku Teks K-13)
     *
     * @return array{nomor:string, judul:string, halaman:?int}|null
     */
    private function bacaSubBab(string $mentah): ?array
    {
        $b = trim($this->normalkanPemandu($mentah));

        if (preg_match('/^([A-Z])\.\s+(.{3,})$/u', $b, $m)) {
            $nomor = $m[1];
            $sisa = $m[2];
        } elseif (preg_match('/^(\d{1,2}\.\d{1,2})\s+(\p{L}.{2,})$/u', $b, $m)) {
            $nomor = $m[1];
            $sisa = $m[2];
        } else {
            return null;
        }

        $pecah = $this->pisahJudulHalaman($sisa);
        $judul = $this->rapikanKapital($pecah['judul']);

        if ($judul === '' || mb_strlen($judul) < 3 || mb_strlen($judul) > 200) {
            return null;
        }

        // Harus mengandung kata sungguhan, bukan sisa titik/angka.
        if (! preg_match('/\p{L}{3,}/u', $judul) || preg_match('/^bab\b/iu', $judul)) {
            return null;
        }

        return ['nomor' => $m[1], 'judul' => $judul, 'halaman' => $pecah['halaman']];
    }

    private function subSudahAda(array $daftar, array $sub): bool
    {
        foreach ($daftar as $x) {
            if ($x['nomor'] === $sub['nomor'] && mb_strtolower($x['judul']) === mb_strtolower($sub['judul'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Memisahkan judul dari nomor halaman di ujung baris daftar isi.
     *
     * @return array{judul:string, halaman:?int}
     */
    private function pisahJudulHalaman(string $s): array
    {
        $s = $this->normalkanPemandu($s);
        $halaman = null;

        // Nomor halaman sesudah titik-titik pemandu atau spasi lebar.
        if (preg_match('/^(.*?)[\s.]{2,}(\d{1,4})\s*$/u', $s, $m)) {
            $s = $m[1];
            $halaman = (int) $m[2];
        } elseif (preg_match('/^(.*?[^\s.])\s+(\d{1,4})\s*$/u', $s, $m)) {
            $s = $m[1];
            $halaman = (int) $m[2];
        }

        return [
            'judul' => $this->bersihkanJudul($s),
            'halaman' => $halaman > 0 && $halaman < 2000 ? $halaman : null,
        ];
    }

    /**
     * Menyeragamkan aneka karakter titik pemandu. Sebagian PDF memakai glif
     * khusus yang terbaca sebagai karakter pengganti, bukan titik biasa.
     */
    private function normalkanPemandu(string $s): string
    {
        $s = str_replace(["\u{00A0}", "\u{2007}", "\u{202F}"], ' ', $s);
        $s = preg_replace('/[\x{FFFD}\x{2024}\x{2026}\x{00B7}\x{2219}\x{22C5}]/u', '.', $s);

        // ". . . ." dan ".  .  ." diseragamkan menjadi rentetan titik.
        return preg_replace('/\.(?:\s+\.){2,}/u', '....', $s);
    }

    /** Membatasi teks ke blok Daftar Isi saja supaya prosa isi buku tidak ikut. */
    private function potongWilayahDaftarIsi(array $baris): array
    {
        $mulai = null;
        $selesai = count($baris);

        foreach ($baris as $i => $b) {
            if ($mulai === null && preg_match('/^\s*Daftar\s+Isi\b/iu', $b)) {
                $mulai = $i;

                continue;
            }

            if ($mulai !== null && $i > $mulai + 5
                && preg_match('/^\s*(Glosarium|Daftar\s+Pustaka|Indeks|Profil\s+Pelaku)\b/iu', $b)) {
                $selesai = $i;
                break;
            }
        }

        return $mulai === null ? $baris : array_slice($baris, $mulai, $selesai - $mulai);
    }

    /**
     * Menyusun judul yang terpenggal di baris-baris sesudah penanda "Bab N".
     *
     * @return array{judul:string, halaman:?int}
     */
    private function judulDariBarisBerikutnya(array $baris, int $i, int $jml): array
    {
        $judul = '';
        $halaman = null;

        for ($j = $i + 1; $j < min($i + 5, $jml); $j++) {
            $mentah = $baris[$j];

            // Baris sub bab menandakan judul babnya sudah selesai.
            if ($this->bacaSubBab($mentah) !== null) {
                break;
            }

            $pecah = $this->pisahJudulHalaman($mentah);
            $c = $pecah['judul'];

            if ($c === '') {
                continue;
            }

            if (preg_match('/^bab\b/iu', $c)) {
                break;
            }

            if (! $this->judulMasukAkal($c)) {
                if ($judul !== '') {
                    break;
                }

                continue;
            }

            $judul = $judul === '' ? $c : $judul.' '.$c;
            $halaman ??= $pecah['halaman'];

            if ($this->adaTitikPemandu($mentah)
                || preg_match('/[!?]$/u', $judul)
                || mb_strlen($judul) > 45) {
                break;
            }
        }

        return ['judul' => $judul, 'halaman' => $halaman];
    }

    /**
     * Menyambung judul yang terpenggal: baris lanjutan dikenali karena tidak
     * punya titik pemandu dan bukan penanda subbab.
     */
    private function sambungJudul(string $judul, array $baris, int $i, int $jml): string
    {
        for ($j = $i + 1; $j < min($i + 3, $jml); $j++) {
            $mentah = $baris[$j];

            if ($this->bacaSubBab($mentah) !== null) {
                break;
            }

            $c = $this->pisahJudulHalaman($mentah)['judul'];

            if ($c === '') {
                continue;
            }

            if ($this->adaTitikPemandu($mentah) || ! $this->judulMasukAkal($c)) {
                break;
            }

            $judul .= ' '.$c;
            break; // Satu baris lanjutan sudah cukup untuk pola dua baris.
        }

        return $judul;
    }

    /** Membuang sisa titik pemandu, nomor halaman, dan penomoran subbab. */
    private function bersihkanJudul(string $s): string
    {
        $s = $this->normalkanPemandu($s);
        $s = strtr($s, self::PERBAIKAN_LIGATUR);

        // Titik-titik pemandu beserta nomor halaman di belakangnya.
        $s = preg_replace('/\s*\.{2,}\s*[ivxlcdm\d]*\s*$/iu', '', $s);
        // Nomor halaman yang dipisah spasi lebar.
        $s = preg_replace('/\s{2,}[ivxlcdm\d]+\s*$/iu', '', $s);
        // Daftar subbab yang ikut terbaca ("Judul A. Subbab B. Subbab").
        $s = preg_replace('/\s+[A-Z]\.\s+\p{Lu}.*$/u', '', $s);
        // Nomor halaman yang menempel langsung di ujung judul.
        $s = preg_replace('/\s+\d{1,3}\s*$/u', '', $s);

        $s = trim(preg_replace('/\s+/u', ' ', $s));

        return trim($s, " \t.|–—-");
    }

    /** Judul yang ditulis KAPITAL SEMUA diseragamkan jadi Kapital Awal. */
    private function rapikanKapital(string $s): string
    {
        // Judul yang sudah punya huruf kecil berarti kapitalisasinya asli.
        if ($s === '' || preg_match('/\p{Ll}/u', $s)) {
            return $s;
        }

        $singkatan = [];
        // Singkatan seperti (LKPD) atau (KA) disimpan dulu agar tidak ikut
        // diubah menjadi huruf kecil.
        $s = preg_replace_callback('/\b\p{Lu}{2,}\b/u', function ($m) use (&$singkatan) {
            $penanda = '@@'.count($singkatan).'@@';
            $singkatan[$penanda] = $m[0];

            return $penanda;
        }, $s);

        $kata = explode(' ', mb_strtolower($s));
        foreach ($kata as $i => $k) {
            if ($k === '') {
                continue;
            }

            // Kata sambung di tengah judul tetap huruf kecil.
            $kata[$i] = ($i > 0 && in_array($k, self::KATA_KECIL, true))
                ? $k
                : Str::ucfirst($k);
        }

        $hasil = implode(' ', $kata);

        // Kembalikan singkatan ke bentuk aslinya.
        foreach ($singkatan as $penanda => $asli) {
            $hasil = str_ireplace($penanda, $asli, $hasil);
        }

        return $hasil;
    }

    /** Menolak baris yang jelas bukan judul bab. */
    private function judulMasukAkal(string $j): bool
    {
        if ($j === '' || mb_strlen($j) < 4 || mb_strlen($j) > 200) {
            return false;
        }

        // Hanya angka/romawi, atau diawali angka (sisa nomor halaman).
        if (preg_match('/^[\d\s.ivxlcdm|]+$/iu', $j) || preg_match('/^\d/u', $j)) {
            return false;
        }

        // Masih mengandung penanda bab lain → baris gabungan yang salah baca.
        if (preg_match('/\bbab\s+([0-9]{1,2}|[ivx]{1,5})\b/iu', $j) || preg_match('/^bab\b/iu', $j)) {
            return false;
        }

        // Penanda subbab ("A. Pendahuluan") bukan judul bab.
        if (preg_match('/^[A-Z]\.\s/u', $j)) {
            return false;
        }

        // Nomor halaman nyelip di tengah judul.
        if (preg_match('/\s\d{2,3}\s/u', $j)) {
            return false;
        }

        if (! preg_match('/\p{L}{3,}/u', $j)) {
            return false;
        }

        $k = mb_strtolower($j);
        foreach (self::BUKAN_JUDUL as $x) {
            if (str_starts_with($k, $x)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Penjaga mutu hasil baca PDF: lebih baik tidak menyimpan apa pun daripada
     * memasukkan judul bab yang salah ke sistem sekolah.
     */
    private function lolosPenjagaMutu(array $bab): bool
    {
        if (count($bab) < 2) {
            return false;
        }

        $pendek = 0;
        foreach ($bab as $b) {
            if (mb_strlen($b['judul']) < 8) {
                $pendek++;
            }
        }

        return $pendek / count($bab) <= 0.34;
    }

    /** Baris daftar isi yang lengkap selalu memakai titik-titik pemandu. */
    private function adaTitikPemandu(string $baris): bool
    {
        return (bool) preg_match('/\.{3,}/u', $this->normalkanPemandu($baris));
    }

    /** Mencari pdftotext: dari konfigurasi, PATH, lalu lokasi umum di Windows. */
    public function pdftotext(): ?string
    {
        $kandidat = array_filter([
            config('services.pdftotext.path'),
            'C:\\Program Files\\Git\\mingw64\\bin\\pdftotext.exe',
            'C:\\laragon\\bin\\poppler\\bin\\pdftotext.exe',
            '/usr/bin/pdftotext',
            '/usr/local/bin/pdftotext',
        ]);

        foreach ($kandidat as $p) {
            if (is_file($p)) {
                return $p;
            }
        }

        @exec('pdftotext -v 2>&1', $keluaran, $kode);

        return $kode === 0 ? 'pdftotext' : null;
    }
}
