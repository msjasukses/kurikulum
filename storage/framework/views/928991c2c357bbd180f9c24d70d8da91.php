<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Dashboard'); ?> - <?php echo e(config('app.name')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f9; }
        .sidebar { width:260px; height:100vh; background:#1e2a3a; color:#cfd8e3; position:fixed; top:0; left:0; overflow-y:auto; }
        .sidebar a { color:#cfd8e3; text-decoration:none; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background:#2c3e50; color:#fff; }
        .sidebar .brand { color:#fff; font-weight:600; padding:1rem; border-bottom:1px solid #2c3e50; }
        .content-wrapper { margin-left:260px; }
        .topbar { background:#fff; border-bottom:1px solid #e3e6ea; }
        .menu-group-title { font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; color:#7d8ba1; padding:.75rem 1rem .25rem; }
        .menu-toggle { width:100%; text-align:left; background:transparent; border:0; color:#cfd8e3; }
        .menu-toggle:hover { background:#2c3e50; color:#fff; }
        .menu-toggle .chev { transition:transform .2s; font-size:.7rem; }
        .menu-toggle.collapsed .chev { transform:rotate(-90deg); }
        .menu-sub .nav-link { padding-left:2.4rem !important; font-size:.9rem; }
        @media (max-width: 991.98px) {
            .sidebar { left:-260px; transition:left .2s; z-index:1040; }
            .sidebar.show { left:0; }
            .content-wrapper { margin-left:0; }
        }
        .badge-role { font-size:.7rem; }
        .sidebar-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1039; }
        .sidebar-backdrop.show { display:block; }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="brand d-flex align-items-center justify-content-between">
        <span><i class="bi bi-mortarboard-fill me-2"></i>SIM Kurikulum</span>
        <button type="button" class="btn btn-sm text-white d-lg-none p-0" onclick="tutupSidebar()" aria-label="Tutup menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <nav class="nav flex-column py-2" id="sidebarNav">
        <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('dashboard')); ?>">
            <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin')): ?>
        <?php ($open = request()->routeIs('setting.*')); ?>
        <button class="menu-toggle nav-link px-3 py-2 d-flex align-items-center <?php echo e($open ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" data-bs-target="#grpSetting">
            <i class="bi bi-gear me-2"></i>Master Data <i class="bi bi-chevron-down ms-auto chev"></i>
        </button>
        <div class="collapse menu-sub <?php echo e($open ? 'show' : ''); ?>" id="grpSetting" data-bs-parent="#sidebarNav">
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('setting.identitas*') ? 'active':''); ?>" href="<?php echo e(route('setting.identitas.edit')); ?>"><i class="bi bi-building me-2"></i>Identitas Sekolah</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('setting.jam-mengajar*') ? 'active':''); ?>" href="<?php echo e(route('setting.jam-mengajar.index')); ?>"><i class="bi bi-clock me-2"></i>Jam Mengajar</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('setting.mata-pelajaran*') ? 'active':''); ?>" href="<?php echo e(route('setting.mata-pelajaran.index')); ?>"><i class="bi bi-journal-bookmark me-2"></i>Mata Pelajaran</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('setting.jenis-eskul*') ? 'active':''); ?>" href="<?php echo e(route('setting.jenis-eskul.index')); ?>"><i class="bi bi-trophy me-2"></i>Jenis Eskul</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('setting.tingkat-kelas*') ? 'active':''); ?>" href="<?php echo e(route('setting.tingkat-kelas.index')); ?>"><i class="bi bi-stack me-2"></i>Tingkat Kelas</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('setting.jurusan*') ? 'active':''); ?>" href="<?php echo e(route('setting.jurusan.index')); ?>"><i class="bi bi-diagram-3 me-2"></i>Jurusan</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('setting.tahun-ajaran*') ? 'active':''); ?>" href="<?php echo e(route('setting.tahun-ajaran.index')); ?>"><i class="bi bi-calendar-range me-2"></i>Tahun Ajaran</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('setting.kelas*') ? 'active':''); ?>" href="<?php echo e(route('setting.kelas.index')); ?>"><i class="bi bi-door-open me-2"></i>Kelas / Rombel</a>
        </div>

        <?php ($open = request()->routeIs('kepegawaian.*')); ?>
        <button class="menu-toggle nav-link px-3 py-2 d-flex align-items-center <?php echo e($open ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" data-bs-target="#grpKepegawaian">
            <i class="bi bi-person-badge me-2"></i>Kepegawaian <i class="bi bi-chevron-down ms-auto chev"></i>
        </button>
        <div class="collapse menu-sub <?php echo e($open ? 'show' : ''); ?>" id="grpKepegawaian" data-bs-parent="#sidebarNav">
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('kepegawaian.pegawai*') ? 'active':''); ?>" href="<?php echo e(route('kepegawaian.pegawai.index')); ?>"><i class="bi bi-person-badge me-2"></i>Data Pegawai</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('kepegawaian.wali-kelas*') ? 'active':''); ?>" href="<?php echo e(route('kepegawaian.wali-kelas.index')); ?>"><i class="bi bi-person-check me-2"></i>Data Wali Kelas</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('kepegawaian.guru-mapel*') ? 'active':''); ?>" href="<?php echo e(route('kepegawaian.guru-mapel.index')); ?>"><i class="bi bi-easel me-2"></i>Data Guru Mata Pelajaran</a>
        </div>

        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['admin','siswa'])): ?>
        <?php ($open = request()->routeIs('kesiswaan.*')); ?>
        <button class="menu-toggle nav-link px-3 py-2 d-flex align-items-center <?php echo e($open ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" data-bs-target="#grpKesiswaan">
            <i class="bi bi-people me-2"></i>Kesiswaan <i class="bi bi-chevron-down ms-auto chev"></i>
        </button>
        <div class="collapse menu-sub <?php echo e($open ? 'show' : ''); ?>" id="grpKesiswaan" data-bs-parent="#sidebarNav">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin')): ?>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('kesiswaan.siswa*') ? 'active':''); ?>" href="<?php echo e(route('kesiswaan.siswa.index')); ?>"><i class="bi bi-people me-2"></i>Data Siswa</a>
            <?php endif; ?>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('kesiswaan.orang-tua*') ? 'active':''); ?>" href="<?php echo e(route('kesiswaan.orang-tua.index')); ?>"><i class="bi bi-person-hearts me-2"></i>Data Orang Tua</a>
        </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['admin','guru'])): ?>
        <?php ($open = request()->routeIs('kurikulum.*')); ?>
        <button class="menu-toggle nav-link px-3 py-2 d-flex align-items-center <?php echo e($open ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" data-bs-target="#grpKurikulum">
            <i class="bi bi-journal-bookmark me-2"></i>Kurikulum <i class="bi bi-chevron-down ms-auto chev"></i>
        </button>
        <div class="collapse menu-sub <?php echo e($open ? 'show' : ''); ?>" id="grpKurikulum" data-bs-parent="#sidebarNav">
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('kurikulum.jadwal*') ? 'active':''); ?>" href="<?php echo e(route('kurikulum.jadwal.index')); ?>"><i class="bi bi-calendar-week me-2"></i>Jadwal Mengajar Guru</a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin')): ?>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('kurikulum.agenda*') ? 'active':''); ?>" href="<?php echo e(route('kurikulum.agenda.index')); ?>"><i class="bi bi-journal-check me-2"></i>Agenda Mengajar</a>
            <?php endif; ?>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('kurikulum.cp-tp-atp*') ? 'active':''); ?>" href="<?php echo e(route('kurikulum.cp-tp-atp.index')); ?>"><i class="bi bi-diagram-2 me-2"></i>Pemetaan CP-TP-ATP</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('kurikulum.modul-ajar*') ? 'active':''); ?>" href="<?php echo e(route('kurikulum.modul-ajar.index')); ?>"><i class="bi bi-file-earmark-text me-2"></i>Modul Ajar Digital</a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin')): ?>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('kurikulum.alokasi-jam*') ? 'active':''); ?>" href="<?php echo e(route('kurikulum.alokasi-jam.index')); ?>"><i class="bi bi-hourglass-split me-2"></i>Alokasi Jam Mapel</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('kurikulum.analisis-guru*') ? 'active':''); ?>" href="<?php echo e(route('kurikulum.analisis-guru.index')); ?>"><i class="bi bi-bar-chart me-2"></i>Analisis Kebutuhan Guru</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php ($open = request()->routeIs('ruangbelajar.*')); ?>
        <button class="menu-toggle nav-link px-3 py-2 d-flex align-items-center <?php echo e($open ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" data-bs-target="#grpRuangBelajar">
            <i class="bi bi-easel me-2"></i>Ruang Belajar <i class="bi bi-chevron-down ms-auto chev"></i>
        </button>
        <div class="collapse menu-sub <?php echo e($open ? 'show' : ''); ?>" id="grpRuangBelajar" data-bs-parent="#sidebarNav">
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('ruangbelajar.materi*') ? 'active':''); ?>" href="<?php echo e(route('ruangbelajar.materi.index')); ?>"><i class="bi bi-play-btn me-2"></i>Materi Online</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('ruangbelajar.tugas*') ? 'active':''); ?>" href="<?php echo e(route('ruangbelajar.tugas.index')); ?>"><i class="bi bi-clipboard-check me-2"></i>Tugas</a>
        </div>

        <?php if (! (auth()->user()->isGuru())): ?>
        <?php ($open = request()->routeIs('absensi.*')); ?>
        <button class="menu-toggle nav-link px-3 py-2 d-flex align-items-center <?php echo e($open ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" data-bs-target="#grpAbsensi">
            <i class="bi bi-clipboard2-check me-2"></i>Absensi <i class="bi bi-chevron-down ms-auto chev"></i>
        </button>
        <div class="collapse menu-sub <?php echo e($open ? 'show' : ''); ?>" id="grpAbsensi" data-bs-parent="#sidebarNav">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin')): ?>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('absensi.rekap-kelas*') ? 'active':''); ?>" href="<?php echo e(route('absensi.rekap-kelas.index')); ?>"><i class="bi bi-table me-2"></i>Rekap per Kelas</a>
            <?php endif; ?>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('absensi.rekap-siswa*') ? 'active':''); ?>" href="<?php echo e(route('absensi.rekap-siswa.index')); ?>"><i class="bi bi-person-lines-fill me-2"></i>Rekap per Siswa</a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin')): ?>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('absensi.koreksi*') ? 'active':''); ?>" href="<?php echo e(route('absensi.koreksi.index')); ?>"><i class="bi bi-pencil-square me-2"></i>Koreksi Absensi</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin')): ?>
        <?php ($open = request()->routeIs('users.*')); ?>
        <button class="menu-toggle nav-link px-3 py-2 d-flex align-items-center <?php echo e($open ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" data-bs-target="#grpUsers">
            <i class="bi bi-shield-lock me-2"></i>Manajemen User <i class="bi bi-chevron-down ms-auto chev"></i>
        </button>
        <div class="collapse menu-sub <?php echo e($open ? 'show' : ''); ?>" id="grpUsers" data-bs-parent="#sidebarNav">
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('users.admin*') ? 'active':''); ?>" href="<?php echo e(route('users.admin.index')); ?>"><i class="bi bi-shield-lock me-2"></i>User Admin</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('users.siswa*') ? 'active':''); ?>" href="<?php echo e(route('users.siswa.index')); ?>"><i class="bi bi-person-vcard me-2"></i>User Siswa</a>
            <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('users.guru*') ? 'active':''); ?>" href="<?php echo e(route('users.guru.index')); ?>"><i class="bi bi-person-workspace me-2"></i>User Guru</a>
        </div>
        <?php endif; ?>

        <div class="menu-group-title">Akun</div>
        <a class="nav-link px-3 py-2 <?php echo e(request()->routeIs('profil.*') ? 'active' : ''); ?>" href="<?php echo e(route('profil.edit')); ?>">
            <i class="bi bi-person-gear me-2"></i> Setting Profil
        </a>
    </nav>
</div>

<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="tutupSidebar()"></div>

<div class="content-wrapper">
    <div class="topbar d-flex align-items-center justify-content-between px-3 py-2">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="bukaTutupSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div></div>
        <div class="d-flex align-items-center gap-2">
        <?php if($tahunAjaranTerpilih->daftar()->isNotEmpty()): ?>
        <div class="dropdown">
            <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-calendar-range me-1"></i>
                <span class="d-none d-sm-inline">T.A. </span><?php echo e($tahunAjaranTerpilih->nama()); ?>

                <?php if($tahunAjaranTerpilih->samaDenganAktif()): ?>
                    <span class="text-muted small d-none d-md-inline">(aktif)</span>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">Pilih Tahun Ajaran</h6></li>
                <?php $__currentLoopData = $tahunAjaranTerpilih->daftar(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <form action="<?php echo e(route('tahun-ajaran.pilih')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="tahun_ajaran_id" value="<?php echo e($ta->id); ?>">
                        <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between <?php echo e($ta->id === $tahunAjaranTerpilih->id() ? 'active' : ''); ?>">
                            <span><?php echo e($ta->nama_tahun_ajaran); ?></span>
                            <?php if($ta->is_aktif): ?>
                                <span class="badge bg-success ms-2">aktif</span>
                            <?php endif; ?>
                        </button>
                    </form>
                </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <?php endif; ?>
        <div class="dropdown">
            <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i> <?php echo e(auth()->user()->name); ?>

                <span class="badge bg-secondary badge-role text-uppercase ms-1"><?php echo e(auth()->user()->role); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="<?php echo e(route('profil.edit')); ?>"><i class="bi bi-person-gear me-2"></i>Setting Profil</a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="<?php echo e(route('logout')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                    </form>
                </li>
            </ul>
        </div>
        </div>
    </div>

    <div class="p-3 p-md-4">
        <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <h4 class="mb-3"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h4>

        <?php echo $__env->yieldContent('content'); ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Buka/tutup sidebar di layar kecil (HP/tablet) + backdrop untuk menutup
    // dengan tap di luar menu.
    function bukaTutupSidebar() {
        var terbuka = document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarBackdrop').classList.toggle('show', terbuka);
    }
    function tutupSidebar() {
        document.getElementById('sidebar').classList.remove('show');
        document.getElementById('sidebarBackdrop').classList.remove('show');
    }

    // Semua elemen <select class="form-select"> di seluruh aplikasi otomatis
    // dijadikan Select2 (dropdown dengan pencarian), termasuk yang di-load
    // lewat halaman baru maupun ditambahkan lewat AJAX/partial di kemudian hari.
    function initSelect2(context) {
        $(context || document).find('select.form-select').each(function () {
            var $sel = $(this);
            if ($sel.hasClass('select2-hidden-accessible')) {
                return; // sudah diinisialisasi
            }
            var placeholderText = $sel.find('option[value=""]').first().text() || 'Pilih...';
            $sel.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: placeholderText,
                allowClear: !$sel.prop('required'),
                language: {
                    noResults: function () { return 'Tidak ada hasil ditemukan'; },
                    searching: function () { return 'Mencari...'; }
                }
            });
        });
    }

    $(function () {
        initSelect2(document);
    });
</script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\kurikulum\resources\views/layouts/app.blade.php ENDPATH**/ ?>