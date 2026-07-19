@extends('layouts.app')
@section('title', 'Analisis Kebutuhan Guru')
@section('content')

{{-- ===================== Form Beban & Aksi ===================== --}}
<div class="card shadow-sm mb-4 d-print-none">
    <div class="card-body">
        <form method="GET" class="d-flex flex-wrap align-items-end gap-2">
            <div>
                <label class="form-label mb-1">Beban (JP)</label>
                <div class="input-group" style="width:180px;">
                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                    <input type="number" name="beban" class="form-control" min="1" value="{{ $beban }}">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-arrow-repeat me-1"></i>Generate
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()" title="Cetak">
                <i class="bi bi-printer"></i>
            </button>
            <a href="{{ route('kurikulum.analisis-guru.excel', ['beban' => $beban]) }}" class="btn btn-outline-success" title="Export Excel">
                <i class="bi bi-file-earmark-excel"></i>
            </a>
        </form>
    </div>
</div>

{{-- ===================== Kop Laporan ===================== --}}
<div class="card shadow-sm mb-4">
    <div class="card-body text-center">
        <h5 class="fw-bold text-uppercase mb-1">Analisis Kebutuhan Guru</h5>
        <h6 class="fw-bold mb-2">{{ optional($identitas)->nama_sekolah ?? '-' }}</h6>
        <div class="small text-muted">
            NPSN: <span class="badge bg-light text-dark border">{{ optional($identitas)->npsn ?? '-' }}</span>
            | Wilayah: <span class="badge bg-light text-dark border">{{ optional($identitas)->kabupaten ?: (optional($identitas)->kecamatan ?: '-') }}</span>
            | Tahun Ajaran: <span class="badge bg-light text-dark border">{{ optional($tahunAktif)->nama_tahun_ajaran ?? '-' }}</span>
            | Beban Standar: <span class="badge bg-light text-dark border">{{ $beban }} JP/Minggu</span>
        </div>
    </div>
</div>

{{-- ===================== Tabel Analisis ===================== --}}
<div class="card shadow-sm">
    <div class="table-responsive">
        @include('kurikulum.partials.analisis-guru-tabel')
    </div>
</div>

<style>
    @media print {
        .sidebar, .topbar, .d-print-none { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        body { background: #fff; }
        .card { border: 0; box-shadow: none !important; }
    }
</style>
@endsection
