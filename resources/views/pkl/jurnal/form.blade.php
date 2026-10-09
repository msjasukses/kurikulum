@extends('layouts.app')
@section('title', 'Isi Jurnal PKL')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="mb-0 fw-semibold text-primary">{{ $item ? 'Ubah' : 'Isi' }} Jurnal PKL</h5>
    <a href="{{ route('pkl.jurnal.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
</div>

@if($item && $item->status === \App\Models\JurnalPkl::STATUS_REVISI)
    <div class="alert alert-warning">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Perlu revisi.</strong>
        {{ $item->catatan_pembimbing ?: 'Perbaiki jurnal lalu simpan kembali.' }}
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <div class="row small text-muted mb-3">
            <div class="col-md-4"><strong class="text-dark">Tanggal:</strong> {{ $tanggal->translatedFormat('l, d F Y') }}</div>
            <div class="col-md-4"><strong class="text-dark">Tempat:</strong> {{ $jadwal->tempat }}</div>
            <div class="col-md-4"><strong class="text-dark">Pembimbing:</strong> {{ $jadwal->pembimbing ?: '-' }}</div>
        </div>

        <form method="POST" action="{{ route('pkl.jurnal.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">

            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <label class="form-label">Jam Mulai</label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai', $item?->jam_mulai ? substr($item->jam_mulai, 0, 5) : '') }}"
                           class="form-control @error('jam_mulai') is-invalid @enderror">
                    @error('jam_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label">Jam Selesai</label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai', $item?->jam_selesai ? substr($item->jam_selesai, 0, 5) : '') }}"
                           class="form-control @error('jam_selesai') is-invalid @enderror">
                    @error('jam_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Kegiatan / Pekerjaan yang Dilakukan <span class="text-danger">*</span></label>
                    <textarea name="kegiatan" rows="5" required class="form-control @error('kegiatan') is-invalid @enderror"
                              placeholder="Uraikan kegiatan yang Anda kerjakan hari ini">{{ old('kegiatan', $item?->kegiatan) }}</textarea>
                    @error('kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Hasil / Kompetensi yang Diperoleh</label>
                    <textarea name="hasil" rows="3" class="form-control @error('hasil') is-invalid @enderror">{{ old('hasil', $item?->hasil) }}</textarea>
                    @error('hasil')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kendala</label>
                    <textarea name="kendala" rows="3" class="form-control @error('kendala') is-invalid @enderror">{{ old('kendala', $item?->kendala) }}</textarea>
                    @error('kendala')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Foto Kegiatan</label>
                    <input type="file" name="foto" accept="image/*" class="form-control @error('foto') is-invalid @enderror">
                    <div class="form-text">Opsional, gambar maks. 5 MB.</div>
                    @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($item?->foto)
                        <img src="{{ Storage::url($item->foto) }}" alt="Foto kegiatan" class="rounded mt-2" style="max-height:120px;">
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Jurnal</button>
            </div>
        </form>
    </div>
</div>

@endsection
