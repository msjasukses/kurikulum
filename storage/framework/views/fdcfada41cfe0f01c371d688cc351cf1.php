<?php $__env->startSection('title', 'Absensi per Mata Pelajaran'); ?>
<?php $__env->startSection('content'); ?>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <div class="col-md-4">
                <label class="form-label">Kelas</label>
                <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php $__currentLoopData = $kelasList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($k->id); ?>" <?php if($kelasId == $k->id): echo 'selected'; endif; ?>><?php echo e($k->nama_rombel); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mata Pelajaran</label>
                <select name="mata_pelajaran_id" class="form-select" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php $__currentLoopData = $mapelList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m->id); ?>" <?php if($mapelId == $m->id): echo 'selected'; endif; ?>><?php echo e($m->nama_mapel); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" value="<?php echo e($tanggal); ?>" class="form-control" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Tampilkan</button>
            </div>
            <div class="col-12">
                <div class="form-text">
                    Kehadiran dicatat per mata pelajaran, jadi satu siswa bisa punya beberapa catatan dalam sehari
                    sesuai jam pelajaran yang diikutinya.
                </div>
            </div>
        </form>
    </div>
</div>

<?php if($rows->isNotEmpty()): ?>
<form method="POST" action="<?php echo e(route('absensi.koreksi.store')); ?>">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="kelas_id" value="<?php echo e($kelasId); ?>">
    <input type="hidden" name="mata_pelajaran_id" value="<?php echo e($mapelId); ?>">
    <input type="hidden" name="tanggal" value="<?php echo e($tanggal); ?>">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <span class="fw-semibold">
                <?php echo e(optional($kelasList->firstWhere('id', (int) $kelasId))->nama_rombel); ?> &mdash;
                <?php echo e(optional($mapelList->firstWhere('id', (int) $mapelId))->nama_mapel); ?>

            </span>
            <span class="badge bg-primary"><?php echo e(\Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('l, d F Y')); ?></span>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Nama Siswa</th><th style="width:180px;">Status</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($i + 1); ?></td>
                        <td>
                            <?php echo e($r['siswa']->nama_siswa); ?>

                            <input type="hidden" name="siswa_id[]" value="<?php echo e($r['siswa']->id); ?>">
                        </td>
                        <td>
                            <select name="status[]" class="form-select form-select-sm">
                                <?php $__currentLoopData = ['Hadir','Izin','Sakit','Alpa']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($st); ?>" <?php if($r['status'] === $st): echo 'selected'; endif; ?>><?php echo e($st); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="keterangan[]" value="<?php echo e($r['keterangan']); ?>" class="form-control form-control-sm">
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Absensi</button>
        </div>
    </div>
</form>
<?php elseif($sudahDipilih): ?>
<div class="alert alert-info">Tidak ada siswa pada kelas tersebut.</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/absensi/koreksi.blade.php ENDPATH**/ ?>