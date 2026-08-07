@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<p class="text-muted">
    Selamat datang, <strong>{{ auth()->user()->name }}</strong>.
    @if($tahunAjaran)
        Data yang ditampilkan untuk Tahun Ajaran
        <span class="badge bg-primary">{{ $tahunAjaran }}</span>
    @endif
</p>

@can('admin')
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center py-3">
            <i class="bi bi-person-badge fs-3 text-primary"></i>
            <div class="fs-4 fw-bold">{{ $stats['pegawai'] }}</div>
            <div class="text-muted small">Pegawai</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center py-3">
            <i class="bi bi-people fs-3 text-success"></i>
            <div class="fs-4 fw-bold">{{ $stats['siswa'] }}</div>
            <div class="text-muted small">Siswa</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center py-3">
            <i class="bi bi-journal-bookmark fs-3 text-warning"></i>
            <div class="fs-4 fw-bold">{{ $stats['mata_pelajaran'] }}</div>
            <div class="text-muted small">Mata Pelajaran</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center py-3">
            <i class="bi bi-easel fs-3 text-info"></i>
            <div class="fs-4 fw-bold">{{ $stats['guru_mapel'] }}</div>
            <div class="text-muted small">Penugasan Guru Mapel</div>
        </div>
    </div>
</div>

{{-- Agenda mengajar yang diisi guru pada hari berjalan --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between">
        <span class="fw-semibold"><i class="bi bi-journal-check me-2"></i>Agenda Mengajar Hari Ini</span>
        <span class="badge bg-primary">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Jam</th>
                    <th>Guru</th>
                    <th>Jam Ke</th>
                    <th>Kelas</th>
                    <th>Mapel</th>
                    <th>Materi</th>
                    <th class="text-center">Hadir/Absen</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agendaHariIni as $a)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $a->waktu_pengisian?->format('H:i') }}</td>
                    <td>{{ optional($a->guru)->nama_ptk ?? '-' }}</td>
                    <td>{{ $a->jam_ke ?: '-' }}</td>
                    <td>{{ optional($a->kelas)->nama_rombel ?? '-' }}</td>
                    <td>{{ optional($a->mataPelajaran)->nama_mapel ?? '-' }}</td>
                    <td>{{ Illuminate\Support\Str::limit($a->materi, 40) }}</td>
                    <td class="text-center"><span class="text-success fw-semibold">{{ $a->hadir }}</span> / <span class="text-danger fw-semibold">{{ $a->absen }}</span></td>
                    <td>
                        @php($badge = ['Disetujui' => 'bg-success', 'Ditolak' => 'bg-danger'][$a->status] ?? 'bg-warning text-dark')
                        <span class="badge {{ $badge }}">{{ $a->status }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-3">Belum ada agenda mengajar yang diisi hari ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white text-end">
        <a href="{{ route('kurikulum.agenda.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua Agenda <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
</div>
@endcan

@if(!auth()->user()->isAdmin())
<div class="card shadow-sm">
    <div class="card-header bg-white">Tugas Terbaru</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light">
                <tr><th>Judul</th><th>Mata Pelajaran</th><th>Kelas</th><th>Batas Akhir</th></tr>
            </thead>
            <tbody>
                @forelse($tugasTerbaru as $t)
                <tr>
                    <td>{{ $t->judul }}</td>
                    <td>{{ $t->mataPelajaran->nama_mapel ?? '-' }}</td>
                    <td>{{ $t->kelas->nama_rombel ?? '-' }}</td>
                    <td>{{ optional($t->tanggal_selesai)->translatedFormat('d M Y') ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada tugas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
