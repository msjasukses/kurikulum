<?php $__env->startSection('title', 'Rekap Absensi per Kelas'); ?>
<?php $__env->startSection('content'); ?>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-3">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php $__currentLoopData = $kelasList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($k->id); ?>" <?php if($kelasId == $k->id): echo 'selected'; endif; ?>><?php echo e($k->nama_rombel); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Mata Pelajaran</label>
                <select name="mata_pelajaran_id" class="form-select">
                    <option value="">Semua Mata Pelajaran</option>
                    <?php $__currentLoopData = $mapelList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m->id); ?>" <?php if($mapelId == $m->id): echo 'selected'; endif; ?>><?php echo e($m->nama_mapel); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="tanggal_mulai" value="<?php echo e($tanggalMulai); ?>" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="tanggal_selesai" value="<?php echo e($tanggalSelesai); ?>" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<?php if($rekap->isNotEmpty()): ?>
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <?php echo e($mapelId ? 'Mata pelajaran: '.optional($mapelList->firstWhere('id', (int) $mapelId))->nama_mapel : 'Seluruh mata pelajaran'); ?>

    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Nama Siswa</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>Total</th>
                    <?php if (! ($mapelId)): ?><th>Rincian per Mata Pelajaran</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $rekap; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($r['siswa']->nama_siswa); ?></td>
                    <td><?php echo e($r['hadir']); ?></td>
                    <td><?php echo e($r['izin']); ?></td>
                    <td><?php echo e($r['sakit']); ?></td>
                    <td><?php echo e($r['alpa']); ?></td>
                    <td><?php echo e($r['total']); ?></td>
                    <?php if (! ($mapelId)): ?>
                    <td>
                        <?php $__empty_1 = true; $__currentLoopData = $r['per_mapel']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <span class="badge bg-light text-dark border me-1"><?php echo e($m['nama']); ?>: <?php echo e($m['hadir']); ?>/<?php echo e($m['total']); ?> hadir</span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php elseif($kelasId): ?>
<div class="alert alert-info">Tidak ada data pada rentang tanggal tersebut.</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/absensi/rekap-kelas.blade.php ENDPATH**/ ?>