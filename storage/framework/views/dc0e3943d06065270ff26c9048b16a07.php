<?php $__env->startSection('title', $title); ?>
<?php $__env->startSection('content'); ?>
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <?php
            $filterAktif = collect($filters ?? [])->contains(fn ($f) => request()->filled($f['name']));
        ?>
        <form class="d-flex flex-wrap gap-2 align-items-center" method="GET">
            <input type="text" name="q" value="<?php echo e($q); ?>" class="form-control form-control-sm" style="max-width:180px;" placeholder="Cari...">
            <?php $__currentLoopData = $filters ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <select name="<?php echo e($filter['name']); ?>" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <option value=""><?php echo e($filter['label']); ?></option>
                    <?php $__currentLoopData = ($filter['options'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value); ?>" <?php if((string) request($filter['name']) === (string) $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
            <?php if($q || $filterAktif): ?>
                <a href="<?php echo e(url()->current()); ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </form>
        <div class="d-flex gap-2">
            <?php if($canManage): ?>
            <a href="<?php echo e(route($routeName.'.create')); ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Tambah
            </a>
            <?php endif; ?>
            <?php $__currentLoopData = $extraActions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e($action['url']); ?>" class="btn btn-sm btn-outline-secondary">
                <?php if(!empty($action['icon'])): ?><i class="bi <?php echo e($action['icon']); ?> me-1"></i><?php endif; ?><?php echo e($action['label']); ?>

            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:40px;">#</th>
                    <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($field['list'] ?? true): ?>
                            <th><?php echo e($field['label']); ?></th>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($loop->iteration + ($items->currentPage()-1) * $items->perPage()); ?></td>
                        <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($field['list'] ?? true): ?>
                                <td>
                                    <?php
                                        $raw = $field['type'] === 'computed' ? null : $item->{$field['name']};
                                        $val = isset($field['relation'])
                                            ? data_get($item, $field['relation']['method'].'.'.$field['relation']['display'])
                                            : ((isset($field['options']) && is_scalar($raw)) ? ($field['options'][$raw] ?? $raw) : $raw);
                                    ?>
                                    <?php if($field['type'] === 'computed'): ?>
                                        <?php $isi = isset($field['render']) ? ($field['render'])($item) : null; ?>
                                        <?php if(is_array($isi)): ?>
                                            <?php $__empty_2 = true; $__currentLoopData = $isi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                <span class="badge <?php echo e($badge['kelas'] ?? 'bg-secondary'); ?> me-1"><?php echo e($badge['teks']); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php echo e($isi ?: '-'); ?>

                                        <?php endif; ?>
                                    <?php elseif($field['type'] === 'file' && $item->{$field['name']}): ?>
                                        <a href="<?php echo e(Storage::url($item->{$field['name']})); ?>" target="_blank">Lihat File</a>
                                    <?php elseif($field['type'] === 'checkbox'): ?>
                                        <?php echo e($item->{$field['name']} ? 'Ya' : 'Tidak'); ?>

                                    <?php elseif($field['type'] === 'checkboxes'): ?>
                                        <?php echo e(Illuminate\Support\Str::limit(implode(', ', (array) $item->{$field['name']}), 60) ?: '-'); ?>

                                    <?php elseif($field['type'] === 'textarea'): ?>
                                        
                                        <?php echo e(Illuminate\Support\Str::limit(App\Support\TeksKaya::polos($val), 60)); ?>

                                    <?php else: ?>
                                        <?php echo e(Illuminate\Support\Str::limit((string) $val, 60)); ?>

                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td class="text-end text-nowrap">
                            <?php $aksiBaris = ($crud ?? null) ? $crud->rowActionsFor($item) : []; ?>
                            <?php $__currentLoopData = $aksiBaris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aksi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e($aksi['url']); ?>" class="btn btn-sm <?php echo e($aksi['class'] ?? 'btn-outline-primary'); ?>" title="<?php echo e($aksi['label']); ?>"
                               <?php if(!empty($aksi['target'])): ?> target="<?php echo e($aksi['target']); ?>" rel="noopener" <?php endif; ?>>
                                <?php if(!empty($aksi['icon'])): ?><i class="bi <?php echo e($aksi['icon']); ?> me-1"></i><?php endif; ?><?php echo e($aksi['label']); ?>

                            </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($canManage): ?>
                            <a href="<?php echo e(route($routeName.'.edit', $item->id)); ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                            <form action="<?php echo e(route($routeName.'.destroy', $item->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            <?php elseif(empty($aksiBaris)): ?>
                                <span class="text-muted small">Lihat saja</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="20" class="text-center text-muted py-4">Belum ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($items->hasPages()): ?>
    <div class="card-footer bg-white">
        <?php echo e($items->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/crud/index.blade.php ENDPATH**/ ?>