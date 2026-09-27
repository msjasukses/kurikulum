<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('content'); ?>
<?php
    $hasFile = collect($fields)->contains(fn($f) => $f['type'] === 'file');
    $pakaiEditor = collect($fields)->contains(fn($f) => !empty($f['editor']));
?>
<div class="card shadow-sm" style="max-width:<?php echo e($pakaiEditor ? '1000px' : '760px'); ?>;">
    <div class="card-header bg-white">
        <?php echo e($item ? 'Ubah' : 'Tambah'); ?> <?php echo e($title); ?>

    </div>
    <div class="card-body">
        <form method="POST"
              action="<?php echo e($item ? route($routeName.'.update', $item->id) : route($routeName.'.store')); ?>"
              <?php if($hasFile): ?> enctype="multipart/form-data" <?php endif; ?>>
            <?php echo csrf_field(); ?>
            <?php if($item): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($field['type'] === 'computed') continue; ?>
                <?php $name = $field['name']; $old = old($name, $item->{$name} ?? null); ?>
                <div class="mb-3">
                    <label class="form-label"><?php echo e($field['label']); ?></label>

                    <?php if(!empty($field['auto'])): ?>
                        
                        <input type="text" class="form-control" value="<?php echo e($field['autoValue'] ?? $old); ?>" disabled>
                        <div class="form-text">Mengikuti tahun ajaran yang dipilih di kanan atas halaman.</div>

                    <?php elseif($field['type'] === 'textarea'): ?>
                        <textarea name="<?php echo e($name); ?>" rows="<?php echo e(!empty($field['editor']) ? 6 : 3); ?>"
                                  class="form-control <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php if(!empty($field['editor'])): ?> editor-html <?php endif; ?>"
                                  placeholder="<?php echo e($field['placeholder'] ?? ''); ?>"><?php echo e($old); ?></textarea>

                    <?php elseif($field['type'] === 'select' && !empty($field['multiple'])): ?>
                        
                        <?php $terpilihSelect = array_map('strval', (array) old($name, $item->{$name} ?? [])); ?>
                        <div id="pilihan-<?php echo e($name); ?>" data-awal="<?php echo e($item->{$name} ?? ''); ?>"
                             class="border rounded p-2 <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="max-height:260px; overflow-y:auto;">
                            <?php $__empty_1 = true; $__currentLoopData = ($field['options'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="<?php echo e($name); ?>-<?php echo e($loop->index); ?>"
                                           name="<?php echo e($name); ?>[]" value="<?php echo e($value); ?>" <?php if(in_array((string) $value, $terpilihSelect, true)): echo 'checked'; endif; ?>>
                                    <label class="form-check-label" for="<?php echo e($name); ?>-<?php echo e($loop->index); ?>"><?php echo e($label); ?></label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-muted small">Belum ada pilihan yang tersedia.</div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text"><?php echo e($field['hint'] ?? 'Boleh dicentang lebih dari satu.'); ?></div>

                    <?php elseif($field['type'] === 'select'): ?>
                        <select name="<?php echo e($name); ?>" class="form-select <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">-- Pilih <?php echo e($field['label']); ?> --</option>
                            <?php $__currentLoopData = ($field['options'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php if((string) $old === (string) $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>

                    <?php elseif($field['type'] === 'checkbox'): ?>
                        <div class="form-check">
                            <input type="hidden" name="<?php echo e($name); ?>" value="0">
                            <input type="checkbox" name="<?php echo e($name); ?>" value="1" class="form-check-input" <?php if($old): echo 'checked'; endif; ?>>
                        </div>

                    <?php elseif($field['type'] === 'checkboxes'): ?>
                        <?php $terpilih = array_map('strval', (array) old($name, $item->{$name} ?? [])); ?>
                        <div class="border rounded p-2 <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-danger <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" style="max-height:220px; overflow-y:auto;">
                            <?php $__empty_1 = true; $__currentLoopData = ($field['options'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="<?php echo e($name); ?>-<?php echo e($loop->index); ?>"
                                           name="<?php echo e($name); ?>[]" value="<?php echo e($value); ?>" <?php if(in_array((string) $value, $terpilih, true)): echo 'checked'; endif; ?>>
                                    <label class="form-check-label" for="<?php echo e($name); ?>-<?php echo e($loop->index); ?>"><?php echo e($label); ?></label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-muted small">Belum ada data master untuk pilihan ini.</div>
                            <?php endif; ?>
                        </div>
                        <div class="form-text">Boleh dicentang lebih dari satu.</div>

                    <?php elseif($field['type'] === 'file'): ?>
                        <input type="file" name="<?php echo e($name); ?>" class="form-control <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php if($item && $item->{$name}): ?>
                            <div class="form-text">File saat ini: <a href="<?php echo e(Storage::url($item->{$name})); ?>" target="_blank">lihat</a></div>
                        <?php endif; ?>

                    <?php elseif($field['type'] === 'password'): ?>
                        <input type="password" name="<?php echo e($name); ?>" class="form-control <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="<?php echo e($item ? 'Kosongkan jika tidak diubah' : ''); ?>">

                    <?php else: ?>
                        <input type="<?php echo e($field['type']); ?>" name="<?php echo e($name); ?>" value="<?php echo e($old); ?>" class="form-control <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="<?php echo e($field['placeholder'] ?? ''); ?>">
                    <?php endif; ?>

                    <?php $__errorArgs = [$name];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                <a href="<?php echo e(route($routeName.'.index')); ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php if($pakaiEditor): ?>
    <?php $__env->startPush('scripts'); ?>
        <?php echo $__env->make('partials.tinymce', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php $__env->stopPush(); ?>
<?php endif; ?>


<?php if(!empty($formScript ?? null)): ?>
    <?php $__env->startPush('scripts'); ?>
        <?php echo $__env->make($formScript, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php $__env->stopPush(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/crud/form.blade.php ENDPATH**/ ?>