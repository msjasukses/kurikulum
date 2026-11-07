<?php

namespace App\Console\Commands;

use App\Services\SibiKatalogService;
use Illuminate\Console\Command;
use Throwable;

/**
 * Mengambil ulang seluruh metadata katalog buku digital dari SIBI dan
 * menyimpannya ke tabel `buku_digital`.
 */
class SinkronKatalogBuku extends Command
{
    protected $signature = 'buku:sinkron
                            {--cover : Sekaligus unduh gambar cover buku ke penyimpanan lokal}';

    protected $description = 'Sinkronkan katalog buku digital dari SIBI (buku.kemendikdasmen.go.id)';

    public function handle(SibiKatalogService $sibi): int
    {
        $this->info('Sinkron katalog buku digital SIBI');

        try {
            $hasil = $sibi->sinkron(fn (string $pesan) => $this->line($pesan));
        } catch (Throwable $e) {
            $this->error('Sinkron gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info(sprintf(
            'Selesai: %d buku (%d baru, %d diperbarui).',
            $hasil['total'],
            $hasil['baru'],
            $hasil['diperbarui']
        ));

        if ($this->option('cover')) {
            $this->newLine();
            $this->unduhSemuaCover($sibi);
        }

        return self::SUCCESS;
    }

    private function unduhSemuaCover(SibiKatalogService $sibi): void
    {
        $belum = \App\Models\BukuDigital::whereNotNull('url_cover')
            ->whereNull('cover_path')
            ->get();

        if ($belum->isEmpty()) {
            $this->line('Semua cover sudah tersimpan.');

            return;
        }

        $this->info("Mengunduh {$belum->count()} gambar cover ...");
        $bar = $this->output->createProgressBar($belum->count());
        $bar->start();
        $berhasil = 0;

        foreach ($belum as $buku) {
            if ($sibi->unduhCover($buku)) {
                $berhasil++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Cover tersimpan: {$berhasil} dari {$belum->count()}.");
    }
}
