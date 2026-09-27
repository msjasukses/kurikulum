@extends('layouts.app')
@section('title', 'Rekap Absensi per Siswa')
@section('content')
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            @if(!$isSiswa)
            <div class="col-md-3">
                <label class="form-label">Siswa</label>
                <select name="siswa_id" class="form-select" required>
                    <option value="">-- Pilih Siswa --</option>
                    @foreach($siswaList as $s)
                        <option value="{{ $s->id }}" @selected($siswaId == $s->id)>{{ $s->nama_siswa }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-3">
                <label class="form-label">Mata Pelajaran</label>
                <select name="mata_pelajaran_id" class="form-select">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($mapelList as $m)
                        <option value="{{ $m->id }}" @selected($mapelId == $m->id)>{{ $m->nama_mapel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

@if($ringkasan->isNotEmpty())
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white"><i class="bi bi-bar-chart me-1"></i>Ringkasan per Mata Pelajaran</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>Mata Pelajaran</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>Total Pertemuan</th><th>Persentase Hadir</th></tr>
            </thead>
            <tbody>
                @foreach($ringkasan as $r)
                @php($persen = $r['total'] ? round($r['hadir'] / $r['total'] * 100) : 0)
                <tr>
                    <td>{{ $r['nama'] }}</td>
                    <td>{{ $r['hadir'] }}</td>
                    <td>{{ $r['izin'] }}</td>
                    <td>{{ $r['sakit'] }}</td>
                    <td>{{ $r['alpa'] }}</td>
                    <td>{{ $r['total'] }}</td>
                    <td><span class="badge bg-{{ $persen >= 75 ? 'success' : ($persen >= 50 ? 'warning text-dark' : 'danger') }}">{{ $persen }}%</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($records->isNotEmpty())
<div class="card shadow-sm">
    <div class="card-header bg-white"><i class="bi bi-list-ul me-1"></i>Rincian Kehadiran</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Tanggal</th><th>Mata Pelajaran</th><th>Status</th><th>Keterangan</th></tr>
            </thead>
            <tbody>
                @foreach($records as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($r->tanggal)->translatedFormat('d M Y') }}</td>
                    <td>{{ optional($r->mataPelajaran)->nama_mapel ?? '-' }}</td>
                    <td><span class="badge bg-{{ $r->status === 'Hadir' ? 'success' : ($r->status === 'Alpa' ? 'danger' : 'warning') }}">{{ $r->status }}</span></td>
                    <td>{{ $r->keterangan }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@elseif($siswaId)
<div class="alert alert-info">Tidak ada data pada rentang tanggal tersebut.</div>
@endif
@endsection
