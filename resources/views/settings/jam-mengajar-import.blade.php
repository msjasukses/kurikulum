@extends('layouts.app')
@section('title', 'Import Jam Mengajar')
@section('content')

@if(session()->has('import_berhasil'))
<div class="alert alert-info">
    Import selesai: <strong>{{ session('import_berhasil') }}</strong> baris berhasil disimpan
    @if(!empty(session('import_gagal')))
        , <strong>{{ count(session('import_gagal')) }}</strong> baris gagal (lihat rincian di bawah).
    @else
        .
    @endif
</div>
@endif

@if(!empty(session('import_gagal')))
<div class="alert alert-warning">
    <div class="fw-bold mb-1">Baris yang gagal diimport:</div>
    <ul class="mb-0">
        @foreach(session('import_gagal') as $pesan)
            <li>{{ $pesan }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card shadow-sm mb-3">
    <div class="card-header bg-white">Import Jam Mengajar dari Excel (.xlsx)</div>
    <div class="card-body">
        <form method="POST" action="{{ route('setting.jam-mengajar-import.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">File Excel</label>
                <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls" required>
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    Kolom yang dibaca: Jam Ke, Tingkat Kelas, Hari, Nama, Jam Mulai, Jam Selesai, Keterangan.
                    Kolom <strong>Tingkat Kelas</strong> dan <strong>Hari</strong> boleh dikosongkan bila jam berlaku
                    untuk semua tingkat/hari. Jam ditulis dengan format <strong>07:00</strong> (boleh juga sel bertipe
                    waktu di Excel). Baris dengan kombinasi Jam Ke + Tingkat Kelas + Hari yang sudah ada akan
                    <strong>ditimpa</strong> (update), kombinasi baru akan ditambahkan.
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import</button>
                <a href="{{ route('setting.jam-mengajar-import.template') }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>Unduh Template</a>
                <a href="{{ route('setting.jam-mengajar.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Daftar Tingkat Kelas yang Valid</div>
            <div class="table-responsive" style="max-height:320px;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Nama Tingkat</th></tr></thead>
                    <tbody>
                        @forelse($tingkatList as $t)
                            <tr><td>{{ $t }}</td></tr>
                        @empty
                            <tr><td class="text-center text-muted py-3">Belum ada data tingkat kelas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Pilihan Hari yang Valid</div>
            <div class="table-responsive" style="max-height:320px;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Hari</th></tr></thead>
                    <tbody>
                        @foreach($hariList as $h)
                            <tr><td>{{ $h }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
