<?php $__env->startSection('title', 'Identitas Sekolah'); ?>
<?php $__env->startSection('content'); ?>
<div class="card shadow-sm" style="max-width:760px;">
    <div class="card-header bg-white">Identitas Sekolah</div>
    <div class="card-body">
        <form method="POST" action="<?php echo e(route('setting.identitas.update')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <?php
                $inputs = [
                    ['npsn', 'NPSN'],
                    ['nama_sekolah', 'Nama Sekolah'],
                    ['kelurahan', 'Kelurahan/Desa'],
                    ['kecamatan', 'Kecamatan'],
                    ['kabupaten', 'Kabupaten/Kota'],
                    ['provinsi', 'Provinsi'],
                    ['kode_pos', 'Kode Pos'],
                    ['telepon', 'Telepon'],
                    ['email', 'Email'],
                    ['website', 'Website'],
                    ['nama_kepala_sekolah', 'Nama Kepala Sekolah'],
                    ['nip_kepala_sekolah', 'NIP Kepala Sekolah'],
                ];
            ?>

            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" rows="2" class="form-control <?php $__errorArgs = ['alamat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('alamat', $identitas->alamat)); ?></textarea>
                <?php $__errorArgs = ['alamat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="row">
                <?php $__currentLoopData = $inputs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$name, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><?php echo e($label); ?></label>
                        <input type="text" name="<?php echo e($name); ?>" value="<?php echo e(old($name, $identitas->{$name})); ?>" class="form-control <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Logo Sekolah</label>
                <input type="file" name="logo" class="form-control <?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <?php if($identitas->logo): ?>
                    <div class="form-text">Logo saat ini: <a href="<?php echo e(Storage::url($identitas->logo)); ?>" target="_blank">lihat</a></div>
                <?php endif; ?>
                <?php $__errorArgs = ['logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/settings/identitas.blade.php ENDPATH**/ ?>