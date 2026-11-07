<?php

namespace App\Console\Commands;

use App\Models\BukuDigital;
use App\Services\EkstraksiBabBuku;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Mengisi daftar bab/topik materi buku digital, diutamakan dari katalog SIBI
 * dan dicadangkan dari Daftar Isi berkas PDF yang sudah diunduh.
 */
class EkstrakBabBuku extends Command
{
    protected $signature = 'buku:bab
                            {--limit=0 : Batasi jumlah buku yang diproses (0 = semua)}
                            {--id=* : Proses buku dengan sibi_id tertentu saja}
                            {--sumber=auto : Sumber data: auto, api, atau pdf}
                            {--semua : Ikut memproses buku yang berkasnya belum diunduh}
                            {--ulang : Proses ulang buku yang babnya sudah terisi}';

    protected $description = 'Ambil daftar bab/topik materi buku digital ke database';

    public function handle(EkstraksiBabBuku $ekstraksi): int
    {
        if ($ekstraksi->pdftotext() === null) {
            $this->warn('pdftotext tidak ditemukan — hanya bab dari API SIBI yang bisa diambil.');
            $this->line('Pasang Poppler lalu isi PDFTOTEXT_PATH di .env untuk mengaktifkan pembacaan PDF.');
            $this->newLine();
        }

        $daftar = $this->daftarBuku();

        if ($daftar->isEmpty()) {
            $this->warn('Tidak ada buku yang perlu diproses.');

            return self::SUCCESS;
        }

        $this->info("Memproses {$daftar->count()} buku ...");
        $this->newLine();

        $ringkasan = ['api' => 0, 'pdf' => 0, 'kosong' => 0, 'bab' => 0, 'sub' => 0, 'adaSub' => 0];

        foreach ($daftar as $buku) {
            $this->line('• '.Str::limit($buku->judul, 68));

            $hasil = $ekstraksi->untukBuku($buku, $this->option('sumber'));

            if ($hasil['bab'] > 0) {
                $ringkasan[$hasil['sumber']]++;
                $ringkasan['bab'] += $hasil['bab'];
                $ringkasan['sub'] += $hasil['sub'];
                $ringkasan['adaSub'] += $hasil['sub'] > 0 ? 1 : 0;
                $warna = $hasil['sumber'] === 'api' ? 'green' : 'yellow';
                $this->line("  <fg={$warna}>✓</> ".$hasil['pesan']);
            } else {
                $ringkasan['kosong']++;
                $this->line('  <fg=gray>–</> '.$hasil['pesan']);
            }
        }

        $this->newLine();
        $this->info(sprintf(
            'Selesai: %d bab + %d sub bab tersimpan — %d buku judul babnya dari API, %d dari PDF, %d tanpa hasil.',
            $ringkasan['bab'],
            $ringkasan['sub'],
            $ringkasan['api'],
            $ringkasan['pdf'],
            $ringkasan['kosong']
        ));
        $this->line("Buku yang dapat rincian sub bab: {$ringkasan['adaSub']} dari ".$daftar->count().'.');

        if ($ringkasan['pdf'] > 0) {
            $this->comment('Bab bersumber "pdf" adalah hasil pembacaan Daftar Isi, jadi perlu diperiksa sekilas.');
        }

        return self::SUCCESS;
    }

    private function daftarBuku()
    {
        $query = BukuDigital::query()->where('tipe', 'pdf');

        if ($ids = array_filter((array) $this->option('id'))) {
            return $query->whereIn('sibi_id', $ids)->get();
        }

        // Baku: hanya buku yang berkasnya sudah ada di lokal, sesuai permintaan.
        if (! $this->option('semua')) {
            $query->whereNotNull('file_path');
        }

        if (! $this->option('ulang')) {
            $query->whereDoesntHave('bab');
        }

        $limit = (int) $this->option('limit');

        return $query->orderBy('id')->when($limit > 0, fn ($q) => $q->limit($limit))->get();
    }
}
