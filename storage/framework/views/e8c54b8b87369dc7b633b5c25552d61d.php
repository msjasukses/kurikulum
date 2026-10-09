<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - <?php echo e($namaSekolah); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root { --hijau:#12b886; --hijau-gelap:#0ca678; }
        body { margin:0; min-height:100vh; font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif; color:#1f2d3d; }
        .login-layar { display:flex; min-height:100vh; }

        .panel-kiri {
            flex:1 1 58%; position:relative; display:flex; flex-direction:column; justify-content:center;
            padding:2.5rem 3.5rem; color:#fff;
            background:linear-gradient(135deg,#0b2545 0%,#123a63 45%,#1d5aa8 100%);
        }
        .panel-kiri .puncak { position:absolute; top:2.25rem; left:3.5rem; right:3.5rem; display:flex; align-items:center; justify-content:space-between; }
        .logo-kotak {
            width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center;
            background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25);
            font-size:1.4rem; font-weight:600; overflow:hidden;
        }
        .logo-kotak img { width:100%; height:100%; object-fit:cover; }
        .tombol-beranda {
            background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25); color:#fff;
            border-radius:999px; padding:.45rem 1.1rem; font-size:.85rem; text-decoration:none; transition:background .15s;
        }
        .tombol-beranda:hover { background:rgba(255,255,255,.22); color:#fff; }
        .panel-kiri h1 { font-size:3rem; font-weight:700; line-height:1.15; margin:0 0 1.25rem; max-width:14ch; }
        .panel-kiri p { font-size:1.05rem; color:rgba(255,255,255,.78); max-width:46ch; margin:0; }
        .panel-kiri .kaki { position:absolute; bottom:2rem; left:3.5rem; right:3.5rem; font-size:.8rem; color:rgba(255,255,255,.6); }

        .panel-kanan { flex:1 1 42%; display:flex; align-items:center; justify-content:center; padding:2.5rem; background:#f7f9fb; }
        .kotak-form { width:100%; max-width:430px; }
        .kotak-form h2 { font-size:1.9rem; font-weight:700; margin:0 0 .35rem; }
        .kotak-form .keterangan { color:#6b7a8d; font-size:.9rem; margin-bottom:1.75rem; }
        .kotak-form label { font-weight:600; font-size:.9rem; margin-bottom:.4rem; }
        .kotak-form .form-control { padding:.7rem .9rem; border-radius:9px; border:1px solid #d7dee6; }
        .kotak-form .form-control:focus { border-color:var(--hijau); box-shadow:0 0 0 .18rem rgba(18,184,134,.18); }
        .grup-sandi { position:relative; }
        .grup-sandi .form-control { padding-right:2.9rem; }
        .grup-sandi button {
            position:absolute; top:0; right:0; height:100%; width:2.9rem; border:0; background:none;
            color:#8a97a6; display:flex; align-items:center; justify-content:center;
        }
        .grup-sandi button:hover { color:var(--hijau-gelap); }
        .btn-masuk {
            width:100%; padding:.8rem; border:0; border-radius:9px; font-weight:600; color:#fff;
            background:var(--hijau); transition:background .15s;
        }
        .btn-masuk:hover { background:var(--hijau-gelap); }
        .catatan-kaki { text-align:center; font-size:.8rem; color:#8a97a6; margin-top:1.25rem; }

        @media (max-width: 991.98px) {
            .login-layar { flex-direction:column; }
            .panel-kiri { flex:0 0 auto; padding:5.5rem 1.75rem 2.5rem; }
            .panel-kiri .puncak, .panel-kiri .kaki { left:1.75rem; right:1.75rem; }
            .panel-kiri .puncak { top:1.5rem; }
            .panel-kiri .kaki { position:static; margin-top:1.75rem; }
            .panel-kiri h1 { font-size:2rem; max-width:none; }
            .panel-kanan { padding:2rem 1.75rem 3rem; }
        }
    </style>
</head>
<body>
<div class="login-layar">

    <div class="panel-kiri">
        <div class="puncak">
            <div class="logo-kotak">
                <?php if(optional($identitas)->logo): ?>
                    <img src="<?php echo e(Storage::url($identitas->logo)); ?>" alt="Logo <?php echo e($namaSekolah); ?>"
                         onerror="this.replaceWith(document.createTextNode('<?php echo e(strtoupper(mb_substr($namaSekolah, 0, 1))); ?>'))">
                <?php else: ?>
                    <?php echo e(strtoupper(mb_substr($namaSekolah, 0, 1))); ?>

                <?php endif; ?>
            </div>
            
            <a href="<?php echo e(optional($identitas)->website ?: '../'); ?>" class="tombol-beranda">&larr; Beranda</a>
        </div>

        <h1>Selamat datang di <?php echo e(config('app.name')); ?>.</h1>
        <p>Kelola jadwal, pemetaan CP-TP-ATP, modul ajar, materi, tugas, dan absensi dalam satu sistem terpadu.</p>

        <div class="kaki">
            &copy; <?php echo e(now()->year); ?> <?php echo e($namaSekolah); ?> &mdash; <?php echo e(config('app.name')); ?>

        </div>
    </div>

    <div class="panel-kanan">
        <div class="kotak-form">
            <h2>Login <?php echo e(config('app.name')); ?></h2>
            <div class="keterangan">Untuk admin, guru, dan siswa sekolah.</div>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 small">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label" for="login">Email / NIP / NISN</label>
                    <input type="text" name="login" id="login" value="<?php echo e(old('login')); ?>" class="form-control"
                           placeholder="Masukkan email, NIP, atau NISN" required autofocus>
                    <div class="form-text">Guru/Siswa memakai NIP/NISN dan kata sandi yang sama dengan aplikasi Data Center.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <div class="grup-sandi">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan kata sandi" required>
                        <button type="button" id="lihat-sandi" aria-label="Tampilkan kata sandi" aria-pressed="false" title="Tampilkan kata sandi">
                            <i class="bi bi-eye" id="ikon-sandi"></i>
                        </button>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
                </div>

                <button type="submit" class="btn-masuk">Login &rarr;</button>
            </form>

            <div class="catatan-kaki">Lupa kata sandi? Hubungi administrator sekolah.</div>
        </div>
    </div>

</div>

<script>
    // Tombol mata: tampilkan/sembunyikan kata sandi yang sedang diketik.
    (function () {
        var tombol = document.getElementById('lihat-sandi');
        var sandi = document.getElementById('password');
        var ikon = document.getElementById('ikon-sandi');

        tombol.addEventListener('click', function () {
            var tampil = sandi.type === 'password';
            sandi.type = tampil ? 'text' : 'password';
            ikon.classList.toggle('bi-eye', !tampil);
            ikon.classList.toggle('bi-eye-slash', tampil);
            tombol.setAttribute('aria-pressed', tampil ? 'true' : 'false');
            tombol.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            tombol.setAttribute('title', tombol.getAttribute('aria-label'));
            sandi.focus();
        });
    })();
</script>
</body>
</html>
<?php /**PATH C:\laragon\www\kurikulum\resources\views/auth/login.blade.php ENDPATH**/ ?>