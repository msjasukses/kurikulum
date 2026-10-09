@extends('layouts.app')
@section('title', 'Detail Jurnal PKL')
@section('content')

@php($isSiswa = auth()->user()->isSiswa())

<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0 fw-semibold text-primary">Detail Jurnal PKL</h5>
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('pkl.jurnal.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="fw-semibold">{{ optional($item->siswa)->nama_siswa }}</div>
                        <div class="small text-muted">
                            {{ $item->tanggal->translatedFormat('l, d F Y') }}
                            @if($item->jam_mulai) &middot; {{ substr($item->jam_mulai, 0, 5) }}–{{ substr((string) $item->jam_selesai, 0, 5) }} @endif
                            <br>{{ $item->tempat }} &middot; Pembimbing: {{ $jadwal?->pembimbing ?: '-' }}
                        </div>
                    </div>
                    <span class="badge bg-{{ $item->warnaStatus() }}">{{ $item->status }}</span>
                </div>

                <h6 class="fw-semibold small text-uppercase text-muted">Kegiatan</h6>
                <p style="white-space:pre-line;">{{ $item->kegiatan }}</p>

                @if($item->hasil)
                    <h6 class="fw-semibold small text-uppercase text-muted">Hasil / Kompetensi</h6>
                    <p style="white-space:pre-line;">{{ $item->hasil }}</p>
                @endif
                @if($item->kendala)
                    <h6 class="fw-semibold small text-uppercase text-muted">Kendala</h6>
                    <p style="white-space:pre-line;">{{ $item->kendala }}</p>
                @endif
                @if($item->foto)
                    <a href="{{ Storage::url($item->foto) }}" target="_blank">
                        <img src="{{ Storage::url($item->foto) }}" alt="Foto kegiatan" class="img-fluid rounded" style="max-height:320px;">
                    </a>
                @endif
            </div>
            @if($isSiswa && $item->bisaDiubahSiswa())
                <div class="card-footer bg-white d-flex gap-2">
                    <a href="{{ route('pkl.jurnal.form', ['tanggal' => $item->tanggal->toDateString()]) }}" class="btn btn-success btn-sm"><i class="bi bi-pencil me-1"></i>Ubah</a>
                    <form method="POST" action="{{ route('pkl.jurnal.destroy', $item) }}" onsubmit="return confirm('Hapus jurnal ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Hapus</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">Pemeriksaan Pembimbing</div>
            <div class="card-body">
                @if($isSiswa)
                    <p class="mb-1 small text-muted">Catatan:</p>
                    <p style="white-space:pre-line;">{{ $item->catatan_pembimbing ?: 'Belum ada catatan.' }}</p>
                @else
                    <form method="POST" action="{{ route('pkl.jurnal.periksa', $item) }}">
                        @csrf
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm mb-2">
                            @foreach(array_keys(\App\Models\JurnalPkl::WARNA_STATUS) as $s)
                                <option value="{{ $s }}" @selected($item->status === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        <label class="form-label small">Catatan untuk siswa</label>
                        <textarea name="catatan_pembimbing" rows="4" class="form-control form-control-sm mb-2">{{ old('catatan_pembimbing', $item->catatan_pembimbing) }}</textarea>
                        <button class="btn btn-primary btn-sm w-100"><i class="bi bi-save me-1"></i>Simpan Pemeriksaan</button>
                    </form>
                    <form method="POST" action="{{ route('pkl.jurnal.destroy', $item) }}" class="mt-2" onsubmit="return confirm('Hapus jurnal ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>Hapus Jurnal</button>
                    </form>
                @endif
                @if($item->diperiksa_pada)
                    <div class="small text-muted mt-3">
                        Diperiksa {{ optional($item->pemeriksa)->name }} &middot; {{ $item->diperiksa_pada->translatedFormat('d M Y H:i') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
