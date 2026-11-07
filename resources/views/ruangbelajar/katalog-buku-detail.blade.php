@extends('layouts.app')
@section('title', 'Detail Buku Digital')
@section('content')

@php($tipe = $buku->penandaTipe())

<div class="mb-3">
    <a href="{{ route('ruangbelajar.katalog-buku.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Katalog
    </a>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-4">
            <div class="col-12 col-md-3">
                @if($buku->urlCover())
                    <img src="{{ $buku->urlCover() }}" alt="Cover {{ $buku->judul }}"
                         class="img-fluid rounded border w-100"
                         onerror="this.replaceWith(Object.assign(document.createElement('div'),{className:'bg-light border rounded d-flex align-items-center justify-content-center text-muted',style:'aspect-ratio:3/4',innerHTML:'<i class=\'bi bi-book\' style=\'font-size:3rem\'></i>'}));">
                @else
                    <div class="bg-light border rounded d-flex align-items-center justify-content-center text-muted"
                         style="aspect-ratio:3/4;">
                        <i class="bi bi-book" style="font-size:3rem;"></i>
                    </div>
                @endif

                <div class="d-grid gap-2 mt-3">
                    @if($buku->tipe === 'pdf' && $buku->urlBaca())
                        <button type="button" class="btn btn-primary" onclick="bukaPembaca()">
                            <i class="bi bi-book-half me-1"></i>Baca di Halaman Ini
                        </button>
                    @elseif($buku->urlBaca())
                        {{-- Buku audio/interaktif tidak bisa ditanam, jadi tetap dibuka di tab baru. --}}
                        <a href="{{ $buku->urlBaca() }}" target="_blank" rel="noopener" class="btn btn-primary">
                            <i class="bi {{ $tipe['icon'] }} me-1"></i>Buka {{ $tipe['teks'] }}
                        </a>
                    @endif

                    @if($bolehKelola && $buku->bisaDiunduh())
                        @if($buku->sudahDiunduh())
                            <div class="alert alert-success py-2 px-3 mb-0 small">
                                <i class="bi bi-hdd-fill me-1"></i>
                                Tersimpan di aplikasi
                                @if($buku->ukuranFileManusiawi())
                                    ({{ $buku->ukuranFileManusiawi() }})
                                @endif
                                @if($buku->diunduh_pada)
                                    <div class="text-muted">{{ $buku->diunduh_pada->translatedFormat('d M Y H:i') }}</div>
                                @endif
                            </div>
                        @else
                            <form method="POST" action="{{ route('ruangbelajar.katalog-buku.unduh', $buku) }}"
                                  onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span>Mengunduh...';">
                                @csrf
                                <button class="btn btn-outline-success w-100">
                                    <i class="bi bi-download me-1"></i>Unduh ke Lokal
                                </button>
                            </form>
                        @endif
                    @endif

                    @if($buku->gagal_unduh && $bolehKelola)
                        <div class="alert alert-warning py-2 px-3 mb-0 small">
                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $buku->gagal_unduh }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-md-9">
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <span class="badge {{ $tipe['kelas'] }}"><i class="bi {{ $tipe['icon'] }} me-1"></i>{{ $tipe['teks'] }}</span>
                    @if($buku->kategori)<span class="badge bg-secondary">{{ $buku->kategori }}</span>@endif
                    @if($buku->jenis_buku)<span class="badge bg-info text-dark">{{ $buku->jenis_buku }}</span>@endif
                    @if($buku->kurikulum)<span class="badge bg-dark">Kurikulum {{ $buku->kurikulum }}</span>@endif
                    @if($buku->sudahDiunduh())<span class="badge bg-success"><i class="bi bi-hdd-fill me-1"></i>Offline</span>@endif
                </div>

                <h5 class="fw-semibold">{{ $buku->judul }}</h5>

                @if(filled($buku->deskripsi))
                    <p class="text-muted">{{ $buku->deskripsi }}</p>
                @endif

                <div class="table-responsive mt-3">
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                            @foreach([
                                'Penulis' => $buku->penulis,
                                'Editor' => $buku->editor,
                                'Ilustrator' => $buku->ilustrator,
                                'Penerbit' => $buku->penerbit,
                                'ISBN' => $buku->isbn,
                                'Jenjang' => $buku->jenjang,
                                'Tingkat Kelas' => $buku->tingkat_kelas,
                                'Mata Pelajaran' => $buku->mata_pelajaran,
                                'Edisi' => $buku->edisi,
                                'Bahasa' => $buku->bahasa,
                                'Tahun Terbit' => $buku->tahun_terbit,
                            ] as $label => $nilai)
                                @if(filled($nilai))
                                <tr>
                                    <th class="text-muted fw-normal" style="width:170px;">{{ $label }}</th>
                                    <td>{{ $nilai }}</td>
                                </tr>
                                @endif
                            @endforeach
                            <tr>
                                <th class="text-muted fw-normal">Tautan Asli</th>
                                <td>
                                    <a href="https://buku.kemendikdasmen.go.id/katalog/{{ $buku->slug }}"
                                       target="_blank" rel="noopener" class="small">
                                        Buka halaman buku di SIBI <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($buku->bab->isNotEmpty())
@php($dariPdf = $buku->bab->first()->dariPdf())
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="fw-semibold">
            <i class="bi bi-list-ol me-2"></i>Topik / Bab Materi
            <span class="badge bg-secondary ms-1">{{ $buku->bab->count() }} bab</span>
            @if($jumlahSub > 0)
                <span class="badge bg-secondary">{{ $jumlahSub }} sub materi</span>
            @endif
        </span>
        @if($dariPdf)
            <span class="badge bg-warning text-dark" title="Dibaca otomatis dari Daftar Isi berkas PDF">
                <i class="bi bi-exclamation-triangle me-1"></i>Hasil baca Daftar Isi
            </span>
        @else
            <span class="badge bg-success" title="Judul bab dari katalog resmi SIBI">
                <i class="bi bi-patch-check me-1"></i>Judul bab dari SIBI
            </span>
        @endif
    </div>

    <div class="list-group list-group-flush">
        @foreach($buku->bab as $b)
        <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="fw-semibold">
                    @if($b->nomor_bab)
                        <span class="text-muted">Bab {{ $b->nomor_bab }}.</span>
                    @endif
                    {{ $b->judul_bab }}
                </div>
                @if($b->halaman)
                    <span class="badge bg-light text-secondary border flex-shrink-0">hlm. {{ $b->halaman }}</span>
                @endif
            </div>

            @if(filled($b->kata_kunci))
                <div class="text-muted small mt-1">
                    <i class="bi bi-tags me-1"></i>{{ $b->kata_kunci }}
                </div>
            @endif

            @if($b->subBab->isNotEmpty())
                {{-- Sub materi selalu hasil baca Daftar Isi PDF, apa pun sumber babnya. --}}
                <ul class="list-unstyled mb-0 mt-2 ms-3 border-start ps-3">
                    @foreach($b->subBab as $s)
                    <li class="d-flex justify-content-between align-items-start gap-2 py-1 small">
                        <span>
                            @if($s->nomor_bab)
                                <span class="text-muted me-1">{{ $s->nomor_bab }}.</span>
                            @endif
                            {{ $s->judul_bab }}
                        </span>
                        @if($s->halaman)
                            <span class="text-muted flex-shrink-0">hlm. {{ $s->halaman }}</span>
                        @endif
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
        @endforeach
    </div>

    @if($dariPdf || $jumlahSub > 0)
        <div class="card-footer bg-white text-muted small">
            @if($dariPdf)
                Daftar bab dan sub materi ini dibaca otomatis dari Daftar Isi berkas PDF,
                sehingga judulnya bisa kurang tepat atau ada bagian yang terlewat.
            @else
                Judul bab berasal dari katalog resmi SIBI, sedangkan rincian sub materi
                dibaca otomatis dari Daftar Isi berkas PDF.
            @endif
        </div>
    @endif
</div>
@endif

@if($buku->tipe === 'pdf' && $buku->urlBaca())
<div class="card shadow-sm mb-3" id="kartuPembaca">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="fw-semibold"><i class="bi bi-book-half me-2"></i>Baca Buku</span>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="aturTinggiPembaca(this)"
                    data-tinggi="sedang" title="Perbesar / perkecil area baca">
                <i class="bi bi-arrows-expand me-1"></i><span>Perbesar</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="pembacaLayarPenuh()">
                <i class="bi bi-fullscreen me-1"></i>Layar Penuh
            </button>
            <a href="{{ route('ruangbelajar.katalog-buku.baca', $buku) }}" target="_blank" rel="noopener"
               class="btn btn-sm btn-outline-secondary" title="Buka di tab baru">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
        </div>
    </div>

    <div class="card-body p-0 position-relative" id="wadahPembaca">
        {{-- Berkas buku bisa puluhan MB, jadi iframe baru diisi saat tombol
             "Baca" ditekan supaya halaman detail tetap ringan dibuka. --}}
        <div id="pembacaPlaceholder" class="text-center text-muted py-5 px-3">
            <i class="bi bi-file-earmark-pdf fs-1 d-block mb-2 text-danger"></i>
            <p class="mb-1 fw-semibold text-dark">{{ \Illuminate\Support\Str::limit($buku->judul, 70) }}</p>
            <p class="small mb-3">
                @if($buku->sudahDiunduh())
                    Berkas tersimpan di aplikasi{{ $buku->ukuranFileManusiawi() ? ' ('.$buku->ukuranFileManusiawi().')' : '' }}.
                @else
                    Berkas dibaca langsung dari server SIBI. Unduh ke lokal agar lebih cepat dan tetap bisa dibuka saat offline.
                @endif
            </p>
            <button type="button" class="btn btn-primary" onclick="bukaPembaca()">
                <i class="bi bi-play-circle me-1"></i>Mulai Baca
            </button>
        </div>

        <iframe id="pembacaBuku" title="Pembaca {{ $buku->judul }}"
                data-src="{{ route('ruangbelajar.katalog-buku.baca', $buku) }}"
                class="w-100 border-0 d-none" style="height:80vh;"></iframe>
    </div>
</div>

@push('scripts')
<script>
    // Pembaca PDF ditanam di halaman memakai penampil bawaan peramban, jadi
    // tidak perlu pustaka tambahan dan tetap punya zoom/cari/cetak sendiri.
    function bukaPembaca(gulirKeSana) {
        var bingkai = document.getElementById('pembacaBuku');
        if (! bingkai) {
            return;
        }

        // Sumber hanya dipasang sekali supaya berkas tidak diunduh berulang.
        if (! bingkai.getAttribute('src')) {
            bingkai.setAttribute('src', bingkai.dataset.src);
        }

        bingkai.classList.remove('d-none');
        var pembuka = document.getElementById('pembacaPlaceholder');
        if (pembuka) {
            pembuka.classList.add('d-none');
        }

        if (gulirKeSana !== false) {
            document.getElementById('kartuPembaca').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Tinggi area baca berputar: sedang (80vh) → tinggi (150vh) → pendek (50vh).
    // Tiap entri berisi [tinggi tujuan, label tombol sesudahnya, nama keadaan tujuan].
    var tinggiPembaca = {
        sedang: ['150vh', 'Perkecil', 'tinggi'],
        tinggi: ['50vh', 'Perbesar', 'pendek'],
        pendek: ['80vh', 'Perbesar', 'sedang']
    };
    function aturTinggiPembaca(tombol) {
        var tujuan = tinggiPembaca[tombol.dataset.tinggi];
        document.getElementById('pembacaBuku').style.height = tujuan[0];
        tombol.querySelector('span').textContent = tujuan[1];
        tombol.dataset.tinggi = tujuan[2];

        // Jangan paksa gulir saat sekadar mengubah ukuran area baca.
        bukaPembaca(false);
    }

    function pembacaLayarPenuh() {
        var wadah = document.getElementById('wadahPembaca');
        bukaPembaca(false);

        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else if (wadah.requestFullscreen) {
            wadah.requestFullscreen();
        }
    }

    // Saat layar penuh iframe dibuat setinggi layar; begitu keluar, tinggi yang
    // tadi dipilih pengguna dikembalikan (bukan dipaksa ke nilai awal).
    var tinggiSebelumLayarPenuh = null;
    document.addEventListener('fullscreenchange', function () {
        var bingkai = document.getElementById('pembacaBuku');
        if (! bingkai) {
            return;
        }

        if (document.fullscreenElement) {
            tinggiSebelumLayarPenuh = bingkai.style.height;
            bingkai.style.height = '100vh';
        } else {
            bingkai.style.height = tinggiSebelumLayarPenuh || '80vh';
        }
    });

    // Datang dari tombol "baca" di katalog: pembaca langsung dibuka.
    @if(request()->boolean('baca'))
        document.addEventListener('DOMContentLoaded', bukaPembaca);
    @endif
</script>
@endpush
@endif

@if($serupa->isNotEmpty())
<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-collection me-2"></i>Buku Terkait
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($serupa as $s)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('ruangbelajar.katalog-buku.show', $s) }}" class="text-decoration-none">
                    @if($s->urlCover())
                        <img src="{{ $s->urlCover() }}" alt="Cover {{ $s->judul }}" loading="lazy"
                             class="img-fluid rounded border mb-1" style="aspect-ratio:3/4; object-fit:cover; width:100%;"
                             onerror="this.style.display='none';">
                    @endif
                    <div class="small text-dark lh-sm">{{ \Illuminate\Support\Str::limit($s->judul, 55) }}</div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection
