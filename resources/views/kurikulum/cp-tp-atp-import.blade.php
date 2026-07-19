@extends('layouts.app')
@section('title', 'Import Pemetaan CP-TP-ATP')
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
    <div class="card-header bg-white">Import Pemetaan CP-TP-ATP dari Excel (.xlsx)</div>
    <div class="card-body">
        <form method="POST" action="{{ route('kurikulum.cp-tp-atp-import.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">File Excel</label>
                <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls" required>
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    Kolom yang dibaca: Kode Mapel, Tingkat Kelas, Fase, Capaian Pembelajaran, Tujuan Pembelajaran,
                    Alur Tujuan Pembelajaran, Tahun Ajaran. Baris dengan kombinasi Mata Pelajaran + Tingkat Kelas +
                    Fase + Tahun Ajaran yang sudah ada akan <strong>ditimpa</strong> (update), kombinasi baru akan ditambahkan.
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import</button>
                <a href="{{ route('kurikulum.cp-tp-atp-import.template') }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>Unduh Template</a>
                <a href="{{ route('kurikulum.cp-tp-atp.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Daftar Kode Mapel yang Valid</div>
            <div class="table-responsive" style="max-height:320px;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Kode</th><th>Nama Mata Pelajaran</th></tr></thead>
                    <tbody>
                        @forelse($mapelList as $m)
                            <tr><td>{{ $m->kode_mapel }}</td><td>{{ $m->nama_mapel }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">Belum ada data mata pelajaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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
</div>

@endsection
