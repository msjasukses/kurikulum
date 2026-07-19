@extends('layouts.app')
@section('title', 'Koreksi Absensi Siswa')
@section('content')
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-5">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" @selected($kelasId == $k->id)>{{ $k->nama_rombel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control" required>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

@if($rows->isNotEmpty())
<form method="POST" action="{{ route('absensi.koreksi.store') }}">
    @csrf
    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
    <div class="card shadow-sm">
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
@elseif($kelasId)
<div class="alert alert-info">Tidak ada siswa pada kelas tersebut.</div>
@endif
@endsection
