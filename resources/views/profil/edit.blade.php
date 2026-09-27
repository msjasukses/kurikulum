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

        @if($bolehAturAi)
        {{-- ===================== Pengaturan AI ===================== --}}
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span><i class="bi bi-stars me-1"></i>Kunci API AI (Generate Modul Ajar)</span>
                @if($user->punyaKunciAi())
                    <span class="badge bg-success">Sudah diatur</span>
                @else
                    <span class="badge bg-secondary">Belum diatur</span>
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Kunci ini dipakai tombol <strong>Generate Modul Ajar</strong> di menu Modul Ajar Digital,
                    dan hanya berlaku untuk akun Anda sendiri. Kunci disimpan dalam bentuk terenkripsi serta
                    tidak pernah ditampilkan kembali secara utuh.
                </p>

                <form method="POST" action="{{ route('profil.ai') }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="ai_provider">Penyedia AI</label>
                            <select name="ai_provider" id="ai_provider" class="form-select @error('ai_provider') is-invalid @enderror">
                                @foreach($penyediaAi as $kode => $info)
                                    <option value="{{ $kode }}" @selected(old('ai_provider', $user->ai_provider) === $kode)>{{ $info['label'] }}</option>
                                @endforeach
                            </select>
                            @error('ai_provider')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">
                                Ambil kunci di:
                                @foreach($penyediaAi as $kode => $info)
                                    <span class="d-none js-alamat-kunci" data-penyedia="{{ $kode }}">
                                        <a href="{{ $info['alamat_kunci'] }}" target="_blank" rel="noopener">{{ $info['alamat_kunci'] }}</a>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="ai_api_key">Kunci API</label>
                            <div class="input-group">
                                <input type="password" name="ai_api_key" id="ai_api_key" autocomplete="off"
                                       class="form-control @error('ai_api_key') is-invalid @enderror"
                                       placeholder="{{ $user->punyaKunciAi() ? 'Tersimpan: '.$user->petunjukKunciAi() : 'Tempelkan kunci API di sini' }}">
                                <button type="button" class="btn btn-outline-secondary" id="lihat-kunci-ai"
                                        aria-label="Tampilkan kunci" title="Tampilkan kunci">
                                    <i class="bi bi-eye" id="ikon-kunci-ai"></i>
                                </button>
                            </div>
                            @error('ai_api_key')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @if($user->punyaKunciAi())
                                <div class="form-text">Kosongkan bila tidak ingin mengganti kunci yang tersimpan.</div>
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="ai_model">Model <span class="text-muted">(opsional)</span></label>
                            <input type="text" name="ai_model" id="ai_model" value="{{ old('ai_model', $user->ai_model) }}"
                                   class="form-control @error('ai_model') is-invalid @enderror">
                            @error('ai_model')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">
                                @foreach($penyediaAi as $kode => $info)
                                    <span class="d-none js-contoh-model" data-penyedia="{{ $kode }}">
                                        Kosongkan untuk memakai <strong>{{ $info['model_bawaan'] }}</strong>.
                                        Pilihan lain: {{ $info['contoh_model'] }}.
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Pengaturan AI</button>
                        @if($user->punyaKunciAi())
                            <button name="hapus_kunci" value="1" class="btn btn-outline-danger"
                                    onclick="return confirm('Hapus kunci API AI dari akun ini?')">
                                <i class="bi bi-trash me-1"></i>Hapus Kunci
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

@if($bolehAturAi)
@push('scripts')
<script>
    // Petunjuk (alamat kunci & contoh model) mengikuti penyedia yang dipilih,
    // plus tombol mata untuk memeriksa kunci yang sedang diketik.
    (function () {
        var $penyedia = $('#ai_provider');

        function segarkanPetunjuk() {
            var kode = $penyedia.val();
            $('.js-alamat-kunci, .js-contoh-model').each(function () {
                $(this).toggleClass('d-none', $(this).data('penyedia') !== kode);
            });
        }

        $penyedia.on('change', segarkanPetunjuk);
        segarkanPetunjuk();

        $('#lihat-kunci-ai').on('click', function () {
            var $kunci = $('#ai_api_key');
            var tampil = $kunci.attr('type') === 'password';
            $kunci.attr('type', tampil ? 'text' : 'password');
            $('#ikon-kunci-ai').toggleClass('bi-eye', !tampil).toggleClass('bi-eye-slash', tampil);
            $(this).attr('aria-label', tampil ? 'Sembunyikan kunci' : 'Tampilkan kunci')
                   .attr('title', tampil ? 'Sembunyikan kunci' : 'Tampilkan kunci');
            $kunci.trigger('focus');
        });
    })();
</script>
@endpush
@endif

@endsection
