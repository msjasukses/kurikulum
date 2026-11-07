<?php

namespace App\Services;

use App\Models\BukuDigital;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Pengambil data katalog buku digital dari SIBI (Sistem Informasi Perbukuan
 * Indonesia) milik Kemendikdasmen — https://buku.kemendikdasmen.go.id/katalog
 *
 * Halaman katalog SIBI dibangun dengan React dan datanya diambil lewat API
 * publiknya, jadi yang dipakai di sini API-nya langsung (bukan membaca HTML).
 */
class SibiKatalogService
{
    /** Alamat dasar API katalog SIBI. */
    private const API = 'https://api.buku.kemendikdasmen.go.id/api/catalogue';

    /**
     * Tiga kelompok katalog yang ditampilkan SIBI. Batas 2000 dipakai supaya
     * seluruh isi tiap kelompok terambil dalam satu permintaan.
     */
    private const FEEDS = [
        'penggerak' => '/getPenggerakTextBooks?limit=2000&order_by=updated_at',
        'nonteks' => '/getNonTextBooks?limit=2000',
        'teks' => '/getTextBooks?limit=2000',
    ];

    /**
     * Subdomain lama tempat berkas buku dulu disimpan. Domain kemdikbud.go.id
     * sudah tidak punya data DNS sejak kementerian berganti nama, sementara
     * API SIBI masih memulangkan tautan lama ini, jadi host-nya ditukar ke
     * domain kemendikdasmen yang masih hidup.
     *
     * Dipakai lewat http:// karena sertifikat TLS host penggantinya tidak
     * cocok dengan nama domainnya (SEC_E_WRONG_PRINCIPAL).
     */
    private const HOST_MATI = 'static.buku.kemdikbud.go.id';

    private const HOST_PENGGANTI = 'http://static.buku.kemendikdasmen.go.id';

    /** Folder penyimpanan berkas hasil unduhan di disk `public`. */
    public const FOLDER_FILE = 'buku-digital/file';

    public const FOLDER_COVER = 'buku-digital/cover';

    /**
     * Menukar host tautan berkas yang sudah mati ke host yang masih melayani
     * berkas yang sama. Tautan dengan host lain dibiarkan apa adanya.
     */
    public static function perbaikiUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        return str_replace(
            ['https://'.self::HOST_MATI, 'http://'.self::HOST_MATI],
            self::HOST_PENGGANTI,
            trim($url)
        );
    }

    private function klien(int $timeout = 60): PendingRequest
    {
        // User-Agent peramban dipakai supaya permintaan tidak ditolak CDN.
        return Http::timeout($timeout)
            ->connectTimeout(20)
            ->retry(2, 1500, throw: false)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '.
                    '(KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                'Accept' => 'application/json, text/plain, */*',
            ]);
    }

    /**
     * Mengambil seluruh isi katalog SIBI lalu menormalkannya ke bentuk kolom
     * tabel `buku_digital`.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ambilKatalog(?callable $lapor = null): array
    {
        $baris = [];

        foreach (self::FEEDS as $namaFeed => $path) {
            $lapor && $lapor("Mengambil katalog: {$namaFeed} ...");

            $respons = $this->klien(120)->get(self::API.$path);

            if (! $respons->successful()) {
                throw new \RuntimeException(
                    "Gagal mengambil katalog {$namaFeed} (HTTP {$respons->status()})."
                );
            }

            $hasil = $respons->json('results') ?? [];
            $lapor && $lapor('  → '.count($hasil).' buku diterima.');

            foreach ($hasil as $buku) {
                if (blank($buku['id'] ?? null)) {
                    continue;
                }

                $baris[(string) $buku['id']] = $this->normalkan($buku, $namaFeed);
            }
        }

        return array_values($baris);
    }

    /**
     * Menyimpan hasil pengambilan katalog ke database. Buku yang sudah ada
     * (dikenali dari sibi_id) diperbarui, sehingga sinkron bisa dijalankan
     * berulang tanpa membuat data dobel.
     *
     * @return array{total:int, baru:int, diperbarui:int}
     */
    public function sinkron(?callable $lapor = null): array
    {
        $baris = $this->ambilKatalog($lapor);

        $idLama = BukuDigital::pluck('sibi_id')->all();
        $baru = 0;
        $diperbarui = 0;

        foreach (array_chunk($baris, 200) as $bagian) {
            foreach ($bagian as $data) {
                $adaSebelumnya = in_array($data['sibi_id'], $idLama, true);

                // Kolom hasil unduhan tidak ikut ditimpa supaya berkas yang
                // sudah tersimpan di lokal tidak kehilangan jejaknya.
                BukuDigital::updateOrCreate(['sibi_id' => $data['sibi_id']], $data);

                $adaSebelumnya ? $diperbarui++ : $baru++;
            }

            $lapor && $lapor('  → tersimpan '.($baru + $diperbarui).' dari '.count($baris).' ...');
        }

        return ['total' => count($baris), 'baru' => $baru, 'diperbarui' => $diperbarui];
    }

    /** Memetakan satu baris dari API SIBI ke kolom tabel `buku_digital`. */
    private function normalkan(array $b, string $namaFeed): array
    {
        return [
            'sibi_id' => (string) $b['id'],
            'slug' => $this->potong($b['slug'] ?? '', 255) ?: Str::slug($b['title'] ?? 'buku'),
            'sumber_feed' => $namaFeed,
            'judul' => $this->potong($b['title'] ?? '(tanpa judul)', 500),
            'penulis' => $this->potong($b['writer'] ?? null, 500),
            'editor' => $this->potong($b['editor'] ?? null, 500),
            'ilustrator' => $this->potong($b['ilustrator'] ?? null, 500),
            'penerbit' => $this->potong($b['publisher'] ?? null, 255),
            'isbn' => $this->potong($b['isbn'] ?? null, 100),
            'bahasa' => $this->potong($b['language'] ?? null, 100),
            'jenjang' => $this->potong($b['level'] ?? null, 100),
            'tingkat_kelas' => $this->potong($b['class'] ?? null, 50),
            'mata_pelajaran' => $this->potong($this->rapikanLabel($b['subject'] ?? null), 150),
            'kategori' => $this->potong($this->rapikanLabel($b['category'] ?? null), 100),
            'jenis_buku' => $this->potong($this->rapikanLabel($b['book_type'] ?? null), 100),
            'kurikulum' => $this->potong($this->rapikanKurikulum($b['curriculum'] ?? null), 50),
            'tipe' => $this->potong($b['type'] ?? 'pdf', 32) ?: 'pdf',
            'edisi' => $this->potong($b['edition'] ?? null, 100),
            'format' => $this->potong($b['format'] ?? null, 100),
            'tahun_terbit' => $this->rapikanTahun($b['published_year'] ?? null),
            'deskripsi' => blank($b['description'] ?? null) ? null : trim(strip_tags($b['description'])),
            'url_cover' => self::perbaikiUrl($b['image'] ?? null),
            'url_file' => self::perbaikiUrl($b['attachment'] ?? null),
            'disinkron_pada' => now(),
        ];
    }

    /**
     * Mengunduh berkas buku (dan cover-nya) ke disk `public`.
     *
     * @return array{ok:bool, pesan:string}
     */
    public function unduhBuku(BukuDigital $buku): array
    {
        // Cover diunduh lebih dulu dan kegagalannya tidak dianggap fatal:
        // katalog tetap berguna walau gambarnya hanya hotlink.
        $this->unduhCover($buku);

        if (! $buku->bisaDiunduh()) {
            return ['ok' => false, 'pesan' => 'Hanya buku PDF yang punya berkas untuk diunduh.'];
        }

        if ($buku->sudahDiunduh()) {
            return ['ok' => true, 'pesan' => 'Berkas sudah ada di penyimpanan lokal.'];
        }

        $namaFile = $this->namaBerkas($buku, 'pdf');
        $tujuan = self::FOLDER_FILE.'/'.$namaFile;
        $pathAbsolut = Storage::disk('public')->path($tujuan);

        Storage::disk('public')->makeDirectory(self::FOLDER_FILE);

        try {
            // Berkas buku bisa sampai puluhan MB, jadi ditulis langsung ke
            // disk (sink) alih-alih ditahan di memori.
            $respons = $this->klien(600)->sink($pathAbsolut)->get($buku->url_file);
        } catch (Throwable $e) {
            @unlink($pathAbsolut);
            $buku->update(['gagal_unduh' => $this->potong($e->getMessage(), 500)]);

            return ['ok' => false, 'pesan' => 'Gagal menghubungi server SIBI: '.$e->getMessage()];
        }

        if (! $respons->successful()) {
            @unlink($pathAbsolut);
            $pesan = "Server SIBI menolak permintaan (HTTP {$respons->status()}).";
            $buku->update(['gagal_unduh' => $pesan]);

            return ['ok' => false, 'pesan' => $pesan];
        }

        // Host pengganti memulangkan halaman HTML 404 untuk berkas yang sudah
        // tidak ada, jadi isi berkas diperiksa tanda tangan PDF-nya.
        if (! $this->berkasPdfValid($pathAbsolut)) {
            @unlink($pathAbsolut);
            $pesan = 'Berkas yang diunduh bukan PDF yang sah (kemungkinan sudah dihapus di SIBI).';
            $buku->update(['gagal_unduh' => $pesan]);

            return ['ok' => false, 'pesan' => $pesan];
        }

        $buku->update([
            'file_path' => $tujuan,
            'ukuran_file' => filesize($pathAbsolut) ?: null,
            'diunduh_pada' => now(),
            'gagal_unduh' => null,
        ]);

        return ['ok' => true, 'pesan' => 'Berkas tersimpan ('.$buku->fresh()->ukuranFileManusiawi().').'];
    }

    /** Mengunduh gambar cover; dilewati bila sudah pernah tersimpan. */
    public function unduhCover(BukuDigital $buku): bool
    {
        if (blank($buku->url_cover)) {
            return false;
        }

        if (filled($buku->cover_path) && Storage::disk('public')->exists($buku->cover_path)) {
            return true;
        }

        $ekstensi = strtolower(pathinfo(parse_url($buku->url_cover, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
        $ekstensi = in_array($ekstensi, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? $ekstensi : 'jpg';

        $tujuan = self::FOLDER_COVER.'/'.$this->namaBerkas($buku, $ekstensi);
        $pathAbsolut = Storage::disk('public')->path($tujuan);

        Storage::disk('public')->makeDirectory(self::FOLDER_COVER);

        try {
            $respons = $this->klien(90)->sink($pathAbsolut)->get($buku->url_cover);
        } catch (Throwable) {
            @unlink($pathAbsolut);

            return false;
        }

        // Pastikan yang tersimpan benar-benar gambar, bukan halaman galat.
        if (! $respons->successful() || ! @getimagesize($pathAbsolut)) {
            @unlink($pathAbsolut);

            return false;
        }

        $buku->update(['cover_path' => $tujuan]);

        return true;
    }

    /** Berkas PDF yang sah selalu dibuka dengan penanda "%PDF". */
    private function berkasPdfValid(string $path): bool
    {
        if (! is_file($path) || filesize($path) < 1024) {
            return false;
        }

        $pegangan = fopen($path, 'rb');
        $kepala = fread($pegangan, 5);
        fclose($pegangan);

        return str_starts_with((string) $kepala, '%PDF');
    }

    /** Nama berkas yang aman dipakai di Windows maupun Linux. */
    private function namaBerkas(BukuDigital $buku, string $ekstensi): string
    {
        $slug = Str::limit(Str::slug($buku->judul ?: 'buku'), 120, '');

        return $buku->sibi_id.'-'.($slug ?: 'buku').'.'.$ekstensi;
    }

    /** "buku_sekolah_penggerak" / "Buku Siswa" → "Buku Sekolah Penggerak". */
    private function rapikanLabel(?string $nilai): ?string
    {
        if (blank($nilai)) {
            return null;
        }

        $rapi = Str::title(str_replace(['_', '-'], ' ', trim($nilai)));

        // Singkatan tetap ditulis kapital ("Buku Pdf" → "Buku PDF").
        return preg_replace_callback(
            '/\b(pdf|isbn|smk|mak|sma|smp|smk|sd|mi|mts|ma|paud|tk)\b/i',
            fn ($cocok) => strtoupper($cocok[1]),
            $rapi
        );
    }

    /** "Kurikulum 2021" dan "2021" sama-sama disimpan sebagai "2021". */
    private function rapikanKurikulum(?string $nilai): ?string
    {
        if (blank($nilai)) {
            return null;
        }

        return preg_match('/(\d{4})/', $nilai, $cocok) ? $cocok[1] : trim($nilai);
    }

    /** SIBI memakai "0000-00-00" untuk tahun yang tidak diisi. */
    private function rapikanTahun(?string $nilai): ?string
    {
        if (blank($nilai) || str_starts_with($nilai, '0000')) {
            return null;
        }

        return preg_match('/(\d{4})/', $nilai, $cocok) ? $cocok[1] : null;
    }

    /** Memotong teks agar tidak melebihi panjang kolomnya di database. */
    private function potong(?string $nilai, int $panjang): ?string
    {
        if ($nilai === null) {
            return null;
        }

        $nilai = trim($nilai);

        return $nilai === '' ? null : Str::limit($nilai, $panjang - 1, '');
    }
}
