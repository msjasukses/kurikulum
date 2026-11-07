@extends('layouts.app')
@section('title', 'Katalog Buku Digital')
@section('content')

@php
    $labelPenyaring = [
        'jenjang' => 'Jenjang',
        'kategori' => 'Kategori',
        'jenis_buku' => 'Jenis Buku',
        'kurikulum' => 'Kurikulum',
        'mata_pelajaran' => 'Mata Pelajaran',
        'tipe' => 'Tipe',
    ];
    $adaPenyaringAktif = collect(array_keys($labelPenyaring))->push('cari', 'unduhan')
        ->contains(fn ($k) => filled(request($k)));
@endphp

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <span class="fw-semibold"><i class="bi bi-book me-2"></i>Buku Teks & Nonteks Kemendikdasmen</span>
                <div class="text-muted small">
                    Sumber: <a href="https://buku.kemendikdasmen.go.id/katalog" target="_blank" rel="noopener">SIBI — Sistem Informasi Perbukuan Indonesia</a>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge bg-primary">{{ number_format($ringkasan['total'], 0, ',', '.') }} buku</span>
                <span class="badge bg-success">{{ number_format($ringkasan['lokal'], 0, ',', '.') }} tersedia offline</span>
                @if($ringkasan['disinkron'])
                    <span class="badge bg-secondary">
                        Sinkron: {{ \Illuminate\Support\Carbon::parse($ringkasan['disinkron'])->translatedFormat('d M Y H:i') }}
                    </span>
                @endif
                @if($bolehKelola)
                <form method="POST" action="{{ route('ruangbelajar.katalog-buku.sinkron') }}"
                      onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span>Mengambil data...';">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary" title="Ambil ulang data katalog dari SIBI">
                        <i class="bi bi-arrow-repeat me-1"></i>Sinkron Katalog
                    </button>
                </form>
                @endif
            </div>
        </div>

        <form method="GET" class="row g-2">
            <div class="col-12 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="cari" value="{{ request('cari') }}" class="form-control"
                           placeholder="Cari judul, penulis, penerbit, atau ISBN...">
                </div>
            </div>

            @foreach($labelPenyaring as $nama => $label)
            <div class="col-6 col-md-4 col-lg-2">
                <select name="{{ $nama }}" class="form-select">
                    <option value="">{{ $label }}: semua</option>
                    @foreach($pilihan[$nama] as $nilai)
                        <option value="{{ $nilai }}" @selected(request($nama) === (string) $nilai)>{{ $nilai }}</option>
                    @endforeach
                </select>
            </div>
            @endforeach

            <div class="col-6 col-md-4 col-lg-2">
                <select name="unduhan" class="form-select">
                    <option value="">Berkas: semua</option>
                    <option value="lokal" @selected(request('unduhan') === 'lokal')>Sudah diunduh</option>
                    <option value="belum" @selected(request('unduhan') === 'belum')>Belum diunduh</option>
                </select>
            </div>

            <div class="col-12 col-lg-auto d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                @if($adaPenyaringAktif)
                    <a href="{{ route('ruangbelajar.katalog-buku.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i>Reset
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($buku->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-center text-muted py-5">
            @if($ringkasan['total'] === 0)
                <i class="bi bi-cloud-arrow-down fs-1 d-block mb-2"></i>
                <p class="mb-1">Katalog masih kosong.</p>
                <p class="small mb-3">
                    Jalankan <code>php artisan buku:sinkron</code>
                    @if($bolehKelola) atau tekan tombol <strong>Sinkron Katalog</strong> di atas @endif
                    untuk mengambil data dari SIBI.
                </p>
            @else
                <i class="bi bi-search fs-1 d-block mb-2"></i>
                <p class="mb-1">Tidak ada buku yang cocok dengan penyaring ini.</p>
                <a href="{{ route('ruangbelajar.katalog-buku.index') }}" class="btn btn-sm btn-outline-secondary mt-2">
                    <i class="bi bi-x-lg me-1"></i>Reset penyaring
                </a>
            @endif
        </div>
    </div>
@else
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted small">
            Menampilkan {{ $buku->firstItem() }}–{{ $buku->lastItem() }} dari
            {{ number_format($buku->total(), 0, ',', '.') }} buku
        </span>
    </div>

    <div class="row g-3">
        @foreach($buku as $b)
        @php($tipe = $b->penandaTipe())
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card h-100 shadow-sm border-0 kartu-buku">
                <a href="{{ route('ruangbelajar.katalog-buku.show', $b) }}" class="text-decoration-none">
                    <div class="posisi-cover bg-light">
                        @if($b->urlCover())
                            <img src="{{ $b->urlCover() }}" alt="Cover {{ $b->judul }}" class="cover-buku"
                                 loading="lazy" onerror="this.closest('.posisi-cover').classList.add('cover-gagal'); this.remove();">
                        @endif
                        <div class="cover-cadangan">
                            <i class="bi bi-book fs-2"></i>
                        </div>
                        <span class="badge {{ $tipe['kelas'] }} penanda-tipe">
                            <i class="bi {{ $tipe['icon'] }}"></i> {{ $tipe['teks'] }}
                        </span>
                        @if($b->sudahDiunduh())
                            <span class="badge bg-success penanda-offline" title="Berkas tersimpan di aplikasi">
                                <i class="bi bi-hdd-fill"></i> Offline
                            </span>
                        @endif
                    </div>
                </a>
                <div class="card-body p-2 d-flex flex-column">
                    <a href="{{ route('ruangbelajar.katalog-buku.show', $b) }}"
                       class="text-decoration-none text-dark fw-semibold small lh-sm mb-1" title="{{ $b->judul }}">
                        {{ \Illuminate\Support\Str::limit($b->judul, 70) }}
                    </a>
                    <div class="text-muted" style="font-size:.72rem;">
                        @if($b->jenjang)<div><i class="bi bi-mortarboard me-1"></i>{{ $b->jenjang }}</div>@endif
                        @if($b->tingkat_kelas)<div><i class="bi bi-stack me-1"></i>Kelas {{ $b->tingkat_kelas }}</div>@endif
                    </div>
                    <div class="mt-auto pt-2 d-flex gap-1">
                        @if($b->urlBaca())
                            {{-- Buku PDF dibaca di halaman detail; audio/interaktif
                                 tidak bisa ditanam jadi dibuka di tab baru. --}}
                            @if($b->tipe === 'pdf')
                                <a href="{{ route('ruangbelajar.katalog-buku.show', ['buku' => $b, 'baca' => 1]) }}"
                                   class="btn btn-sm btn-outline-primary flex-fill" title="Baca buku di aplikasi">
                                    <i class="bi bi-book-half"></i>
                                </a>
                            @else
                                <a href="{{ $b->urlBaca() }}" target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-primary flex-fill" title="Buka buku">
                                    <i class="bi bi-book-half"></i>
                                </a>
                            @endif
                        @endif
                        @if($bolehKelola && $b->bisaDiunduh() && ! $b->sudahDiunduh())
                            <form method="POST" action="{{ route('ruangbelajar.katalog-buku.unduh', $b) }}" class="flex-fill">
                                @csrf
                                <button class="btn btn-sm btn-outline-success w-100" title="Unduh berkas ke aplikasi"
                                        onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm\'></span>'; this.form.submit();">
                                    <i class="bi bi-download"></i>
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('ruangbelajar.katalog-buku.show', $b) }}"
                           class="btn btn-sm btn-outline-secondary" title="Detail buku">
                            <i class="bi bi-info-circle"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-3">
        {{ $buku->links() }}
    </div>
@endif

@push('scripts')
<style>
    .kartu-buku { transition:transform .15s, box-shadow .15s; }
    .kartu-buku:hover { transform:translateY(-3px); box-shadow:0 .5rem 1rem rgba(0,0,0,.12) !important; }
    /* Kotak cover dibuat rasio tetap supaya tinggi kartu seragam walau ukuran
       gambar dari SIBI bermacam-macam. */
    .posisi-cover { position:relative; padding-top:133%; overflow:hidden; border-radius:.375rem .375rem 0 0; }
    .cover-buku { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
    .cover-cadangan { position:absolute; inset:0; display:none; align-items:center; justify-content:center; color:#adb5bd; }
    /* Tampil hanya kalau gambarnya gagal dimuat atau tidak ada sama sekali. */
    .posisi-cover.cover-gagal .cover-cadangan,
    .posisi-cover:not(:has(.cover-buku)) .cover-cadangan { display:flex; }
    .penanda-tipe { position:absolute; top:.35rem; left:.35rem; font-size:.62rem; }
    .penanda-offline { position:absolute; top:.35rem; right:.35rem; font-size:.62rem; }
</style>
@endpush

@endsection
