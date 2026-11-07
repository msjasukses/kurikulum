<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku_digital', function (Blueprint $table) {
            $table->id();

            // Identitas asal dari katalog SIBI (buku.kemendikdasmen.go.id).
            // sibi_id dipakai sebagai kunci sinkron supaya buku yang sama tidak
            // dobel saat katalog diambil ulang.
            $table->string('sibi_id', 32)->unique();
            $table->string('slug')->index();
            $table->string('sumber_feed', 32)->index();

            $table->string('judul', 500);
            $table->string('penulis', 500)->nullable();
            $table->string('editor', 500)->nullable();
            $table->string('ilustrator', 500)->nullable();
            $table->string('penerbit', 255)->nullable();
            $table->string('isbn', 100)->nullable();
            $table->string('bahasa', 100)->nullable();

            // Kolom penyaring di halaman katalog.
            $table->string('jenjang', 100)->nullable()->index();
            $table->string('tingkat_kelas', 50)->nullable()->index();
            $table->string('mata_pelajaran', 150)->nullable()->index();
            $table->string('kategori', 100)->nullable()->index();
            $table->string('jenis_buku', 100)->nullable()->index();
            $table->string('kurikulum', 50)->nullable()->index();
            $table->string('tipe', 32)->default('pdf')->index();

            $table->string('edisi', 100)->nullable();
            $table->string('format', 100)->nullable();
            $table->string('tahun_terbit', 20)->nullable();
            $table->text('deskripsi')->nullable();

            // Tautan asli di server SIBI; dipakai sebagai cadangan bila file
            // lokal belum diunduh.
            $table->text('url_cover')->nullable();
            $table->text('url_file')->nullable();

            // Hasil unduhan ke disk `public`.
            $table->string('cover_path')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('ukuran_file')->nullable();
            $table->timestamp('diunduh_pada')->nullable();
            $table->text('gagal_unduh')->nullable();

            $table->timestamp('disinkron_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_digital');
    }
};
