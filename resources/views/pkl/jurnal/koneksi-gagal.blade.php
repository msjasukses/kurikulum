@extends('layouts.app')
@section('title', 'Jurnal PKL / Magang')
@section('content')

<h5 class="mb-3 fw-semibold text-primary">Jurnal PKL / Magang</h5>

<div class="alert alert-warning">
    <strong><i class="bi bi-plug me-1"></i>Jadwal magang tidak dapat dibaca.</strong>
    Aplikasi tidak bisa terhubung ke database aplikasi Absensi (tabel <code>jadwal_magang</code>).
    @can('admin')
        Periksa pengaturan <code>ABSENSI_DB_*</code> di file <code>.env</code>, lalu jalankan <code>php artisan config:clear</code>.
    @else
        Silakan hubungi administrator sekolah.
    @endcan
</div>

@endsection
