@extends('layouts.app')
@section('title', 'Jurnal PKL / Magang')
@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <h5 class="mb-0 fw-semibold text-primary">Jurnal PKL / Magang</h5>
    <span class="small text-muted"><i class="bi bi-info-circle me-1"></i>Jadwal magang mengikuti menu Jadwal Magang di aplikasi Absensi.</span>
</div>

@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif

<form method="GET" class="card shadow-sm mb-3">
    <div class="card-body row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1">Cari siswa / NIS / pembimbing</label>
            <input type="text" name="q" value="{{ $filter['q'] }}" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Tempat Magang</label>
            <select name="tempat" class="form-select form-select-sm">
                <option value="">Semua tempat</option>
                @foreach($tempatList as $t)
                    <option value="{{ $t }}" @selected($filter['tempat'] === $t)>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Status Jurnal</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach(array_keys(\App\Models\JurnalPkl::WARNA_STATUS) as $s)
                    <option value="{{ $s }}" @selected($filter['status'] === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Dari</label>
            <input type="date" name="dari" value="{{ $filter['dari'] }}" class="form-control form-control-sm">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Sampai</label>
            <input type="date" name="sampai" value="{{ $filter['sampai'] }}" class="form-control form-control-sm">
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Terapkan</button>
            <a href="{{ route('pkl.jurnal.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </div>
</form>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold">Rekap Keterisian Jurnal per Siswa</div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0" style="font-size:.85rem;">
            <thead class="table-light text-center">
                <tr>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Tempat Magang</th>
                    <th>Pembimbing</th>
                    <th>Periode</th>
                    <th title="Hari magang yang sudah berjalan s.d. hari ini">Hari Magang</th>
                    <th>Terisi</th>
                    <th>Menunggu</th>
                    <th>Disetujui</th>
                    <th>Cetak</th>
                </tr>
            </thead>
            <tbody>
            @forelse($rekap as $r)
                @php($persen = $r['wajib'] ? round($r['terisi'] / $r['wajib'] * 100) : 0)
                <tr>
                    <td class="text-center">{{ $r['jadwal']->nis }}</td>
                    <td>{{ optional($r['siswa'])->nama_siswa ?? '(siswa tidak ditemukan di datacenter)' }}</td>
                    <td>{{ $r['jadwal']->tempat }}</td>
                    <td>{{ $r['jadwal']->pembimbing ?: '-' }}</td>
                    <td class="text-nowrap small">
                        {{ $r['jadwal']->tanggal_mulai->format('d/m/Y') }} &ndash; {{ $r['jadwal']->tanggal_selesai->format('d/m/Y') }}<br>
                        <span class="text-muted">{{ $r['jadwal']->namaHari() }}</span>
                    </td>
                    <td class="text-center">{{ $r['wajib'] }}</td>
                    <td class="text-center">
                        {{ $r['terisi'] }}
                        <div class="progress mt-1" style="height:4px;">
                            <div class="progress-bar {{ $persen >= 80 ? 'bg-success' : ($persen >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ min($persen, 100) }}%"></div>
                        </div>
                    </td>
                    <td class="text-center">{{ $r['menunggu'] ?: '-' }}</td>
                    <td class="text-center">{{ $r['disetujui'] ?: '-' }}</td>
                    <td class="text-center">
                        @if($r['siswa'])
                            <a href="{{ route('pkl.jurnal.cetak', ['siswa_id' => $r['siswa']->id, 'jadwal_id' => $r['jadwal']->id]) }}" target="_blank"
                               class="btn btn-sm btn-outline-success" title="Cetak jurnal"><i class="bi bi-printer"></i></a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted py-4">Belum ada jadwal magang yang berjalan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-semibold">Daftar Jurnal Harian</div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0" style="font-size:.85rem;">
            <thead class="text-white text-center" style="background:#3f51b5;">
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Siswa</th>
                    <th>Tempat</th>
                    <th>Jam</th>
                    <th>Kegiatan</th>
                    <th>Foto</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($items as $j)
                <tr>
                    <td class="text-nowrap">{{ $j->tanggal->translatedFormat('D, d M Y') }}</td>
                    <td>{{ optional($j->siswa)->nama_siswa ?? '-' }}</td>
                    <td>{{ $j->tempat }}</td>
                    <td class="text-center text-nowrap">{{ $j->jam_mulai ? substr($j->jam_mulai, 0, 5).'–'.substr((string) $j->jam_selesai, 0, 5) : '-' }}</td>
                    <td style="max-width:320px;">{{ \Illuminate\Support\Str::limit($j->kegiatan, 120) }}</td>
                    <td class="text-center">
                        @if($j->foto)
                            <a href="{{ Storage::url($j->foto) }}" target="_blank">
                                <img src="{{ Storage::url($j->foto) }}" alt="Foto" class="rounded" style="width:48px;height:36px;object-fit:cover;">
                            </a>
                        @else - @endif
                    </td>
                    <td class="text-center"><span class="badge bg-{{ $j->warnaStatus() }}">{{ $j->status }}</span></td>
                    <td class="text-center text-nowrap">
                        <a href="{{ route('pkl.jurnal.show', $j) }}" class="btn btn-sm btn-primary" title="Periksa"><i class="bi bi-check2-square"></i></a>
                        @if($j->status !== \App\Models\JurnalPkl::STATUS_DISETUJUI)
                            <form method="POST" action="{{ route('pkl.jurnal.periksa', $j) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="{{ \App\Models\JurnalPkl::STATUS_DISETUJUI }}">
                                <button class="btn btn-sm btn-success" title="Setujui cepat"><i class="bi bi-check-lg"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada jurnal yang sesuai filter.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center">
        <small class="text-muted">Records: {{ $items->count() }} of {{ $items->total() }}</small>
        {{ $items->links() }}
    </div>
</div>

@endsection
