<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('content'); ?>

<p class="text-muted">
    Selamat datang, <strong><?php echo e(auth()->user()->name); ?></strong>.
    <?php if($tahunAjaran): ?>
        Data yang ditampilkan untuk Tahun Ajaran
        <span class="badge bg-primary"><?php echo e($tahunAjaran); ?></span>
    <?php endif; ?>
</p>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('admin')): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center py-3">
            <i class="bi bi-person-badge fs-3 text-primary"></i>
            <div class="fs-4 fw-bold"><?php echo e($stats['pegawai']); ?></div>
            <div class="text-muted small">Pegawai</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center py-3">
            <i class="bi bi-people fs-3 text-success"></i>
            <div class="fs-4 fw-bold"><?php echo e($stats['siswa']); ?></div>
            <div class="text-muted small">Siswa</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center py-3">
            <i class="bi bi-journal-bookmark fs-3 text-warning"></i>
            <div class="fs-4 fw-bold"><?php echo e($stats['mata_pelajaran']); ?></div>
            <div class="text-muted small">Mata Pelajaran</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm text-center py-3">
            <i class="bi bi-easel fs-3 text-info"></i>
            <div class="fs-4 fw-bold"><?php echo e($stats['guru_mapel']); ?></div>
            <div class="text-muted small">Penugasan Guru Mapel</div>
        </div>
    </div>
</div>


<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between">
        <span class="fw-semibold"><i class="bi bi-journal-check me-2"></i>Agenda Mengajar Hari Ini</span>
        <span class="badge bg-primary"><?php echo e(now()->locale('id')->translatedFormat('l, d F Y')); ?></span>
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
                <?php $__empty_1 = true; $__currentLoopData = $agendaHariIni; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($a->waktu_pengisian?->format('H:i')); ?></td>
                    <td><?php echo e(optional($a->guru)->nama_ptk ?? '-'); ?></td>
                    <td><?php echo e($a->jam_ke ?: '-'); ?></td>
                    <td><?php echo e(optional($a->kelas)->nama_rombel ?? '-'); ?></td>
                    <td><?php echo e(optional($a->mataPelajaran)->nama_mapel ?? '-'); ?></td>
                    <td><?php echo e(Illuminate\Support\Str::limit($a->materi, 40)); ?></td>
                    <td class="text-center"><span class="text-success fw-semibold"><?php echo e($a->hadir); ?></span> / <span class="text-danger fw-semibold"><?php echo e($a->absen); ?></span></td>
                    <td>
                        <?php ($badge = ['Disetujui' => 'bg-success', 'Ditolak' => 'bg-danger'][$a->status] ?? 'bg-warning text-dark'); ?>
                        <span class="badge <?php echo e($badge); ?>"><?php echo e($a->status); ?></span>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="9" class="text-center text-muted py-3">Belum ada agenda mengajar yang diisi hari ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white text-end">
        <a href="<?php echo e(route('kurikulum.agenda.index')); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua Agenda <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
</div>
<?php endif; ?>

<?php if(!auth()->user()->isAdmin()): ?>
<div class="card shadow-sm">
    <div class="card-header bg-white">Tugas Terbaru</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light">
                <tr><th>Judul</th><th>Mata Pelajaran</th><th>Kelas</th><th>Batas Akhir</th></tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $tugasTerbaru; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($t->judul); ?></td>
                    <td><?php echo e($t->mataPelajaran->nama_mapel ?? '-'); ?></td>
                    <td><?php echo e($t->kelas->nama_rombel ?? '-'); ?></td>
                    <td><?php echo e(optional($t->tanggal_selesai)->translatedFormat('d M Y') ?? '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4" class="text-center text-muted py-3">Belum ada tugas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/dashboard/index.blade.php ENDPATH**/ ?>