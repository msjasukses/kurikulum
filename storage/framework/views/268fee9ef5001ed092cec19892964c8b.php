<?php $__env->startSection('title', 'Import Jam Mengajar'); ?>
<?php $__env->startSection('content'); ?>

<?php if(session()->has('import_berhasil')): ?>
<div class="alert alert-info">
    Import selesai: <strong><?php echo e(session('import_berhasil')); ?></strong> baris berhasil disimpan
    <?php if(!empty(session('import_gagal'))): ?>
        , <strong><?php echo e(count(session('import_gagal'))); ?></strong> baris gagal (lihat rincian di bawah).
    <?php else: ?>
        .
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if(!empty(session('import_gagal'))): ?>
<div class="alert alert-warning">
    <div class="fw-bold mb-1">Baris yang gagal diimport:</div>
    <ul class="mb-0">
        <?php $__currentLoopData = session('import_gagal'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pesan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($pesan); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-white">Import Jam Mengajar dari Excel (.xlsx)</div>
    <div class="card-body">
        <form method="POST" action="<?php echo e(route('setting.jam-mengajar-import.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label">File Excel</label>
                <input type="file" name="file" class="form-control <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" accept=".xlsx,.xls" required>
                <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <div class="form-text">
                    Kolom yang dibaca: Jam Ke, Tingkat Kelas, Hari, Nama, Jam Mulai, Jam Selesai, Keterangan.
                    Kolom <strong>Tingkat Kelas</strong> dan <strong>Hari</strong> boleh dikosongkan bila jam berlaku
                    untuk semua tingkat/hari. Jam ditulis dengan format <strong>07:00</strong> (boleh juga sel bertipe
                    waktu di Excel). Baris dengan kombinasi Jam Ke + Tingkat Kelas + Hari yang sudah ada akan
                    <strong>ditimpa</strong> (update), kombinasi baru akan ditambahkan.
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import</button>
                <a href="<?php echo e(route('setting.jam-mengajar-import.template')); ?>" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>Unduh Template</a>
                <a href="<?php echo e(route('setting.jam-mengajar.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Daftar Tingkat Kelas yang Valid</div>
            <div class="table-responsive" style="max-height:320px;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Nama Tingkat</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $tingkatList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr><td><?php echo e($t); ?></td></tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td class="text-center text-muted py-3">Belum ada data tingkat kelas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">Pilihan Hari yang Valid</div>
            <div class="table-responsive" style="max-height:320px;">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Hari</th></tr></thead>
                    <tbody>
                        <?php $__currentLoopData = $hariList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr><td><?php echo e($h); ?></td></tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/settings/jam-mengajar-import.blade.php ENDPATH**/ ?>