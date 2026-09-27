@extends('layouts.app')
@section('title', 'Kumpulkan Tugas')
@section('content')

@if(session('error'))
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}</div>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="fw-semibold"><i class="bi bi-journal-text me-2"></i>{{ $tugas->judul }}</span>
        @if($tugas->tanggal_selesai)
            <span class="badge {{ $lewatBatas ? 'bg-danger' : 'bg-success' }}">
                Batas akhir: {{ $tugas->tanggal_selesai->translatedFormat('d F Y') }}
                {{ $lewatBatas ? '(sudah lewat)' : '' }}
            </span>
        @endif
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Mata Pelajaran</dt>
            <dd class="col-sm-9">{{ optional($tugas->mataPelajaran)->nama_mapel ?? '-' }}</dd>
            <dt class="col-sm-3">Kelas</dt>
            <dd class="col-sm-9">{{ optional($tugas->kelas)->nama_rombel ?? '-' }}</dd>
            <dt class="col-sm-3">Guru</dt>
            <dd class="col-sm-9">{{ optional($tugas->pegawai)->nama_ptk ?? '-' }}</dd>
            @if(filled($tugas->deskripsi))
            <dt class="col-sm-3">Instruksi</dt>
            <dd class="col-sm-9">{!! App\Support\TeksKaya::html($tugas->deskripsi) !!}</dd>
            @endif
            @if($tugas->file_lampiran)
            <dt class="col-sm-3">Lampiran Guru</dt>
            <dd class="col-sm-9"><a href="{{ Storage::url($tugas->file_lampiran) }}" target="_blank"><i class="bi bi-paperclip me-1"></i>Unduh lampiran</a></dd>
            @endif
        </dl>
    </div>
</div>

@if($pengumpulan && $pengumpulan->sudahDinilai())
<div class="card shadow-sm mb-3 border-success">
    <div class="card-header bg-white"><i class="bi bi-patch-check me-1 text-success"></i>Sudah Dinilai</div>
    <div class="card-body">
        <div class="fs-3 fw-bold text-success mb-2">{{ $pengumpulan->nilai }}</div>
        @if(filled($pengumpulan->umpan_balik))
            <div class="fw-semibold small">Umpan balik guru:</div>
            <p class="mb-0">{{ $pengumpulan->umpan_balik }}</p>
        @endif
    </div>
</div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <i class="bi bi-upload me-1"></i>{{ $pengumpulan ? 'Jawaban Saya' : 'Kumpulkan Jawaban' }}
        @if($pengumpulan && $pengumpulan->dikumpulkan_pada)
            <span class="badge {{ $pengumpulan->terlambat ? 'bg-warning text-dark' : 'bg-success' }} ms-1">
                Dikumpulkan {{ $pengumpulan->dikumpulkan_pada->translatedFormat('d F Y H:i') }}
                {{ $pengumpulan->terlambat ? '(terlambat)' : '' }}
            </span>
        @endif
    </div>
    <div class="card-body">
        @if($pengumpulan && $pengumpulan->sudahDinilai())
            <p class="text-muted mb-3">Tugas sudah dinilai guru sehingga jawaban tidak bisa diubah lagi.</p>
            @if(filled($pengumpulan->catatan))
                <div class="fw-semibold small">Catatan yang dikirim:</div>
                <p>{{ $pengumpulan->catatan }}</p>
            @endif
            @if($pengumpulan->file)
                <a href="{{ Storage::url($pengumpulan->file) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i>File yang dikirim
                </a>
            @endif
        @else
            @if($lewatBatas)
                <div class="alert alert-warning py-2">
                    <i class="bi bi-clock-history me-1"></i>Batas waktu sudah lewat. Tugas tetap bisa dikumpulkan,
                    tetapi akan ditandai <strong>terlambat</strong>.
                </div>
            @endif

            <form method="POST" action="{{ route('ruangbelajar.pengumpulan.store', $tugas->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="catatan">Catatan / Jawaban</label>
                    <textarea name="catatan" id="catatan" rows="5"
                              class="form-control @error('catatan') is-invalid @enderror"
                              placeholder="Tulis jawaban atau keterangan singkat tentang tugasmu...">{{ old('catatan', $pengumpulan->catatan ?? '') }}</textarea>
                    @error('catatan')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="file">File Jawaban <span class="text-muted">(opsional)</span></label>
                    <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror">
                    @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="form-text">
                        Maksimal 10 MB. Format: pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip, rar, jpg, png.
                        @if($pengumpulan && $pengumpulan->file)
                            <br>File saat ini:
                            <a href="{{ Storage::url($pengumpulan->file) }}" target="_blank">lihat</a>
                            — mengunggah file baru akan menggantikannya.
                        @endif
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>{{ $pengumpulan ? 'Perbarui Jawaban' : 'Kumpulkan Tugas' }}
                    </button>
                    <a href="{{ route('ruangbelajar.tugas.index') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        @endif
    </div>
</div>

@endsection
