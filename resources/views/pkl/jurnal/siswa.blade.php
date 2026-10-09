@extends('layouts.app')
@section('title', 'Jurnal PKL / Magang')
@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <h5 class="mb-0 fw-semibold text-primary">Jurnal PKL / Magang</h5>
    @if($jadwalList->isNotEmpty())
        <div class="d-flex flex-wrap gap-2">
            @if($hariIniMagang)
                <a href="{{ route('pkl.jurnal.form', ['tanggal' => today()->toDateString()]) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil-square me-1"></i>Isi Jurnal Hari Ini
                </a>
            @endif
            <a href="{{ route('pkl.jurnal.cetak') }}" target="_blank" class="btn btn-success btn-sm">
                <i class="bi bi-printer me-1"></i>Cetak Jurnal
            </a>
        </div>
    @endif
</div>

@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif

@if($jadwalList->isEmpty())
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-1"></i>
        Anda belum terdaftar di jadwal magang/PKL. Jadwal diatur oleh admin di aplikasi Absensi.
    </div>
@else
    <div class="row g-3 mb-3">
        @foreach($jadwalList as $j)
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h6 class="fw-semibold mb-1"><i class="bi bi-building me-1"></i>{{ $j->tempat }}</h6>
                            @if($j->berlakuPada(today()) || today()->between($j->tanggal_mulai, $j->tanggal_selesai))
                                <span class="badge bg-success">Berjalan</span>
                            @elseif(today()->lt($j->tanggal_mulai))
                                <span class="badge bg-secondary">Belum mulai</span>
                            @else
                                <span class="badge bg-dark">Selesai</span>
                            @endif
                        </div>
                        <div class="small text-muted">
                            {{ $j->tanggal_mulai->translatedFormat('d F Y') }} &ndash; {{ $j->tanggal_selesai->translatedFormat('d F Y') }}<br>
                            Hari magang: {{ $j->namaHari() }}<br>
                            Pembimbing: {{ $j->pembimbing ?: '-' }}
                            @if($j->keterangan)<br>Keterangan: {{ $j->keterangan }}@endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-semibold">Hari Magang s.d. Hari Ini</span>
            <span class="small text-muted">
                Terisi <strong>{{ $jumlahTerisi }}</strong> dari <strong>{{ $hariMagang->count() }}</strong> hari
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:.9rem;">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Tempat</th>
                        <th>Kegiatan</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($hariMagang as $h)
                    @php($jr = $h['jurnal'])
                    <tr>
                        <td class="text-nowrap">{{ $h['tanggal']->translatedFormat('l, d M Y') }}</td>
                        <td>{{ $h['jadwal']->tempat }}</td>
                        <td style="max-width:360px;">{{ $jr ? \Illuminate\Support\Str::limit($jr->kegiatan, 90) : '' }}</td>
                        <td class="text-center">
                            @if($jr)
                                <span class="badge bg-{{ $jr->warnaStatus() }}">{{ $jr->status }}</span>
                            @else
                                <span class="badge bg-light text-muted border">Belum diisi</span>
                            @endif
                        </td>
                        <td class="text-end text-nowrap">
                            @if($jr)
                                <a href="{{ route('pkl.jurnal.show', $jr) }}" class="btn btn-sm btn-outline-secondary" title="Lihat"><i class="bi bi-eye"></i></a>
                            @endif
                            @if(! $jr || $jr->bisaDiubahSiswa())
                                <a href="{{ route('pkl.jurnal.form', ['tanggal' => $h['tanggal']->toDateString()]) }}"
                                   class="btn btn-sm {{ $jr ? 'btn-success' : 'btn-primary' }}" title="{{ $jr ? 'Ubah' : 'Isi' }}">
                                    <i class="bi bi-{{ $jr ? 'pencil' : 'plus-lg' }}"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada hari magang yang berjalan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
