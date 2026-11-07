<?php

namespace App\Http\Controllers\RuangBelajar;

use App\Http\Controllers\Controller;
use App\Models\BukuDigital;
use App\Services\EkstraksiBabBuku;
use App\Services\SibiKatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Katalog buku digital: menampilkan buku hasil sinkron dari SIBI, menyediakan
 * penyaring/pencarian, serta tombol mengunduh berkas buku ke penyimpanan lokal.
 */
class KatalogBukuController extends Controller
{
    /** Penyaring yang tersedia di halaman katalog: nama query → kolom tabel. */
    private const PENYARING = [
        'jenjang' => 'jenjang',
        'kategori' => 'kategori',
        'jenis_buku' => 'jenis_buku',
        'kurikulum' => 'kurikulum',
        'tipe' => 'tipe',
        'mata_pelajaran' => 'mata_pelajaran',
    ];

    public function __construct(
        private readonly SibiKatalogService $sibi,
        private readonly EkstraksiBabBuku $ekstraksiBab,
    ) {
    }

    public function index(Request $request)
    {
        $query = BukuDigital::query()->cari($request->input('cari'));

        foreach (self::PENYARING as $namaQuery => $kolom) {
            $query->when(
                filled($request->input($namaQuery)),
                fn ($q) => $q->where($kolom, $request->input($namaQuery))
            );
        }

        // Penyaring khusus status berkas lokal.
        $query->when($request->input('unduhan') === 'lokal', fn ($q) => $q->whereNotNull('file_path'))
            ->when($request->input('unduhan') === 'belum', fn ($q) => $q->whereNull('file_path'));

        $buku = $query
            ->orderByRaw('file_path IS NULL')   // buku yang sudah tersedia offline tampil dulu
            ->orderBy('judul')
            ->paginate(24)
            ->withQueryString();

        return view('ruangbelajar.katalog-buku', [
            'buku' => $buku,
            'pilihan' => $this->pilihanPenyaring(),
            'ringkasan' => $this->ringkasan(),
            'bolehKelola' => $this->bolehKelola(),
        ]);
    }

    public function show(BukuDigital $buku)
    {
        $buku->load('bab.subBab');

        return view('ruangbelajar.katalog-buku-detail', [
            'buku' => $buku,
            'bolehKelola' => $this->bolehKelola(),
            'jumlahSub' => $buku->bab->sum(fn ($b) => $b->subBab->count()),
            // Buku serupa: mapel yang sama atau jenjang yang sama. Kedua syarat
            // dikurung agar tidak membatalkan pengecualian buku ini sendiri.
            'serupa' => BukuDigital::where('id', '!=', $buku->id)
                ->where(function ($q) use ($buku) {
                    $q->when($buku->mata_pelajaran, fn ($w, $v) => $w->orWhere('mata_pelajaran', $v))
                        ->when($buku->jenjang, fn ($w, $v) => $w->orWhere('jenjang', $v));
                })
                ->orderByRaw('file_path IS NULL')
                ->limit(6)
                ->get(),
        ]);
    }

    /**
     * Menyajikan berkas buku untuk dibaca di dalam halaman (iframe).
     *
     * Berkas yang sudah diunduh dikirim langsung dari disk supaya satu origin
     * dengan aplikasi; yang belum diunduh dialihkan ke berkas asli di SIBI
     * agar tidak perlu menyalurkan puluhan MB lewat PHP.
     */
    public function baca(BukuDigital $buku)
    {
        abort_unless($buku->tipe === 'pdf', 404, 'Buku ini bukan berkas PDF.');

        if ($buku->sudahDiunduh()) {
            return response()->file(
                Storage::disk('public')->path($buku->file_path),
                [
                    'Content-Type' => 'application/pdf',
                    // "inline" membuat peramban menampilkannya, bukan mengunduh.
                    'Content-Disposition' => 'inline; filename="'.Str::slug($buku->judul).'.pdf"',
                ]
            );
        }

        abort_if(blank($buku->url_file), 404, 'Buku ini belum punya berkas.');

        return redirect()->away($buku->url_file);
    }

    /** Mengunduh satu berkas buku dari SIBI ke penyimpanan lokal aplikasi. */
    public function unduh(BukuDigital $buku)
    {
        abort_unless($this->bolehKelola(), 403);

        // Berkas buku bisa puluhan MB, jadi batas waktu eksekusi dilonggarkan.
        @set_time_limit(600);

        $hasil = $this->sibi->unduhBuku($buku);

        if (! $hasil['ok']) {
            return back()->with('error', 'Gagal mengunduh "'.$buku->judul.'": '.$hasil['pesan']);
        }

        // Berkasnya baru tersedia, jadi daftar bab & sub materi langsung
        // diambil di sini supaya pengguna tidak perlu menjalankan perintah
        // terpisah sebelum bisa melihatnya.
        $bab = $this->ekstraksiBab->untukBukuDenganAman($buku->fresh());

        return back()->with(
            'success',
            '"'.$buku->judul.'" berhasil diunduh ke penyimpanan lokal. '
                .$hasil['pesan'].' '.$bab['pesan']
        );
    }

    /** Mengambil ulang seluruh metadata katalog dari SIBI. */
    public function sinkron()
    {
        abort_unless($this->bolehKelola(), 403);

        @set_time_limit(900);

        try {
            $hasil = $this->sibi->sinkron();
        } catch (Throwable $e) {
            return back()->with('error', 'Sinkron katalog gagal: '.$e->getMessage());
        }

        return back()->with('success', sprintf(
            'Sinkron katalog selesai: %d buku (%d baru, %d diperbarui).',
            $hasil['total'],
            $hasil['baru'],
            $hasil['diperbarui']
        ));
    }

    /** Siswa hanya boleh melihat & membaca, tidak mengunduh atau menyinkron. */
    private function bolehKelola(): bool
    {
        return auth()->user() && ! auth()->user()->isSiswa();
    }

    /**
     * Daftar nilai unik tiap kolom penyaring, diambil langsung dari data yang
     * ada supaya pilihannya selalu mengikuti isi katalog.
     *
     * @return array<string, array<int, string>>
     */
    private function pilihanPenyaring(): array
    {
        $pilihan = [];

        foreach (self::PENYARING as $namaQuery => $kolom) {
            $pilihan[$namaQuery] = BukuDigital::query()
                ->whereNotNull($kolom)
                ->where($kolom, '!=', '')
                ->distinct()
                ->orderBy($kolom)
                ->pluck($kolom)
                ->all();
        }

        return $pilihan;
    }

    /** @return array{total:int, lokal:int, pdf:int, disinkron:?string} */
    private function ringkasan(): array
    {
        return [
            'total' => BukuDigital::count(),
            'lokal' => BukuDigital::whereNotNull('file_path')->count(),
            'pdf' => BukuDigital::where('tipe', 'pdf')->count(),
            'disinkron' => BukuDigital::max('disinkron_pada'),
        ];
    }
}
