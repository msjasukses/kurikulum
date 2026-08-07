@extends('layouts.app')
@section('title', 'Setting Profil')
@section('content')

@php
    $roleLabel = [
        'admin' => 'Administrator',
        'guru' => 'Guru',
        'siswa' => 'Siswa',
    ][$user->role] ?? ucfirst($user->role);

    $hakAkses = [
        'admin' => ['Seluruh menu Master Data, Kepegawaian, Kesiswaan', 'Kurikulum, Ruang Belajar, Absensi (rekap & koreksi)', 'Manajemen User (admin, guru, siswa)'],
        'guru' => ['Kurikulum: jadwal, CP-TP-ATP, modul ajar, agenda mengajar', 'Ruang Belajar: materi online & tugas', 'Absensi: rekap kelas & koreksi'],
        'siswa' => ['Ruang Belajar: materi online & tugas', 'Absensi: rekap kehadiran pribadi', 'Kesiswaan: data orang tua (lihat saja)'],
    ][$user->role] ?? [];
@endphp

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:88px;height:88px;font-size:2rem;">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <div class="text-muted small mb-2">{{ $user->email }}</div>
                <span class="badge bg-secondary text-uppercase">{{ $roleLabel }}</span>
                <span class="badge {{ $user->aktif ? 'bg-success' : 'bg-danger' }}">{{ $user->aktif ? 'Aktif' : 'Nonaktif' }}</span>
            </div>
            @if($hakAkses)
            <div class="card-footer bg-white">
                <div class="fw-semibold small mb-2"><i class="bi bi-shield-check me-1"></i>Hak Akses</div>
                <ul class="small text-muted mb-0 ps-3">
                    @foreach($hakAkses as $akses)
                        <li>{{ $akses }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white"><i class="bi bi-person-lines-fill me-1"></i>Data Diri</div>
            <div class="card-body">
                <div class="row">
                    @foreach($detail as [$label, $nilai])
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small">{{ $label }}</div>
                            <div>{{ $nilai ?: '-' }}</div>
                        </div>
                    @endforeach
                </div>
                @unless($akunLokal)
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Data diri Anda bersumber dari aplikasi <strong>Datacenter</strong> dan bersifat lihat saja.
                        Perubahan data dilakukan di aplikasi Datacenter, lalu otomatis tersinkron saat Anda login kembali.
                    </div>
                @endunless
            </div>
        </div>

        @if($akunLokal)
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white"><i class="bi bi-person-gear me-1"></i>Akun Login</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profil.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white"><i class="bi bi-key me-1"></i>Ganti Kata Sandi</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profil.password') }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kata Sandi Lama</label>
                            <input type="password" name="password_lama" class="form-control @error('password_lama') is-invalid @enderror" required>
                            @error('password_lama')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kata Sandi Baru</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ulangi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-shield-lock me-1"></i>Ubah Kata Sandi</button>
                </form>
            </div>
        </div>
        @else
        <div class="card shadow-sm">
            <div class="card-header bg-white"><i class="bi bi-key me-1"></i>Kata Sandi</div>
            <div class="card-body">
                <p class="mb-0 text-muted">
                    Kata sandi Anda diverifikasi langsung ke aplikasi <strong>Datacenter</strong> memakai
                    {{ $user->isSiswa() ? 'NISN' : 'NIP' }} dan kata sandi yang sama seperti login di sana.
                    Silakan ubah kata sandi lewat aplikasi Datacenter.
                </p>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
