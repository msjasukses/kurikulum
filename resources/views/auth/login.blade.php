<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ $namaSekolah }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { min-height:100vh; display:flex; align-items:center; background:linear-gradient(135deg,#1e2a3a,#2c3e50); }
        .login-card { max-width:400px; width:100%; margin:auto; border-radius:1rem; }
    </style>
</head>
<body>
    <div class="card login-card shadow-lg p-4">
        <div class="text-center mb-3">
            <i class="bi bi-mortarboard-fill" style="font-size:2.5rem;color:#2c3e50;"></i>
            <h5 class="mt-2 mb-1">{{ $namaSekolah }}</h5>
            <div class="text-muted small">{{ config('app.name') }}</div>
            <small class="text-muted">Silakan masuk untuk melanjutkan</small>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email / NIP / NISN</label>
                <input type="text" name="login" value="{{ old('login') }}" class="form-control"
                       placeholder="Email akun, NIP guru, atau NISN siswa" required autofocus>
                <div class="form-text">Guru/Siswa: masuk memakai NIP/NISN dan password yang sama dengan aplikasi Data Center.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Kata Sandi</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <button type="submit" class="btn btn-dark w-100">Masuk</button>
        </form>
    </div>
</body>
</html>
