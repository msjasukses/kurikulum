@extends('layouts.app')
@section('title', 'Absensi per Mata Pelajaran')
@section('content')
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-4">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" @selected($kelasId == $k->id)>{{ $k->nama_rombel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mata Pelajaran</label>
                <select name="mata_pelajaran_id" class="form-select" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach($mapelList as $m)
                        <option value="{{ $m->id }}" @selected($mapelId == $m->id)>{{ $m->nama_mapel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Tampilkan</button>
            </div>
            <div class="col-12">
                <div class="form-text">
                    Kehadiran dicatat per mata pelajaran, jadi satu siswa bisa punya beberapa catatan dalam sehari
                    sesuai jam pelajaran yang diikutinya.
                </div>
            </div>
        </form>
    </div>
</div>

@if($rows->isNotEmpty())
<form method="POST" action="{{ route('absensi.koreksi.store') }}">
    @csrf
    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
    <input type="hidden" name="mata_pelajaran_id" value="{{ $mapelId }}">
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <span class="fw-semibold">
                {{ optional($kelasList->firstWhere('id', (int) $kelasId))->nama_rombel }} &mdash;
                {{ optional($mapelList->firstWhere('id', (int) $mapelId))->nama_mapel }}
            </span>
            <span class="badge bg-primary">{{ \Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Nama Siswa</th><th style="width:180px;">Status</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $r)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            {{ $r['siswa']->nama_siswa }}
                            <input type="hidden" name="siswa_id[]" value="{{ $r['siswa']->id }}">
                        </td>
                        <td>
                            <select name="status[]" class="form-select form-select-sm">
                                @foreach(['Hadir','Izin','Sakit','Alpa'] as $st)
                                    <option value="{{ $st }}" @selected($r['status'] === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" name="keterangan[]" value="{{ $r['keterangan'] }}" class="form-control form-control-sm">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Absensi</button>
        </div>
    </div>
</form>
@elseif($sudahDipilih)
<div class="alert alert-info">Tidak ada siswa pada kelas tersebut.</div>
@endif
@endsection
