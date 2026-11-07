<?php

namespace App\Console\Commands;

use App\Models\BukuDigital;
use App\Services\EkstraksiBabBuku;
use App\Services\SibiKatalogService;
use Illuminate\Console\Command;

/**
 * Mengunduh berkas PDF buku dari SIBI ke penyimpanan lokal aplikasi
 * (storage/app/public/buku-digital/file).
 */
class UnduhBukuDigital extends Command
{
    protected $signature = 'buku:unduh
                            {--limit=10 : Jumlah buku yang diunduh pada sekali jalan}
                            {--jenjang= : Batasi pada jenjang tertentu, mis. "SMK/MAK"}
                            {--kategori= : Batasi pada kategori tertentu, mis. "Buku Teks"}
                            {--id=* : Unduh buku dengan sibi_id tertentu saja}
                            {--ulang : Coba lagi buku yang sebelumnya gagal diunduh}
                            {--tanpa-bab : Lewati pengambilan daftar bab sesudah mengunduh}';

    protected $description = 'Unduh berkas PDF buku digital dari SIBI ke penyimpanan lokal';

    public function handle(SibiKatalogService $sibi, EkstraksiBabBuku $ekstraksiBab): int
    {
        $daftar = $this->daftarBuku();

        if ($daftar->isEmpty()) {
            $this->warn('Tidak ada buku yang perlu diunduh (mungkin semuanya sudah tersimpan).');

            return self::SUCCESS;
        }

        $this->info("Mengunduh {$daftar->count()} berkas buku ...");
        $this->newLine();

        $berhasil = 0;
        $gagal = 0;
        $babTotal = 0;
        $subTotal = 0;

        foreach ($daftar as $buku) {
            $this->line('• '.\Illuminate\Support\Str::limit($buku->judul, 70));

            $hasil = $sibi->unduhBuku($buku);

            if (! $hasil['ok']) {
                $gagal++;
                $this->line('  <fg=red>✗</> '.$hasil['pesan']);

                continue;
            }

            $berhasil++;
            $this->line('  <fg=green>✓</> '.$hasil['pesan']);

            // Daftar bab langsung diambil begitu berkasnya ada, supaya buku
            // yang baru diunduh tidak tampil tanpa materi di halaman katalog.
            if (! $this->option('tanpa-bab')) {
                $bab = $ekstraksiBab->untukBukuDenganAman($buku->fresh());
                $babTotal += $bab['bab'];
                $subTotal += $bab['sub'];
                $this->line('    <fg=cyan>↳</> '.$bab['pesan']);
            }
        }

        $this->newLine();
        $this->info("Selesai: {$berhasil} berhasil, {$gagal} gagal.");

        if (! $this->option('tanpa-bab') && $berhasil > 0) {
            $this->line("Daftar materi terisi: {$babTotal} bab + {$subTotal} sub materi.");
        }

        return self::SUCCESS;
    }

    /** Buku PDF yang belum punya berkas lokal, sesuai penyaring yang diberikan. */
    private function daftarBuku()
    {
        $query = BukuDigital::query()
            ->where('tipe', 'pdf')
            ->whereNotNull('url_file')
            ->whereNull('file_path');

        if ($ids = array_filter((array) $this->option('id'))) {
            return $query->whereIn('sibi_id', $ids)->get();
        }

        // Buku yang sudah pernah gagal dilewati, kecuali diminta mencoba ulang,
        // supaya percobaan berikutnya tidak tersangkut di berkas yang hilang.
        if (! $this->option('ulang')) {
            $query->whereNull('gagal_unduh');
        }

        return $query
            ->when($this->option('jenjang'), fn ($q, $v) => $q->where('jenjang', $v))
            ->when($this->option('kategori'), fn ($q, $v) => $q->where('kategori', $v))
            ->orderBy('id')
            ->limit(max(1, (int) $this->option('limit')))
            ->get();
    }
}
