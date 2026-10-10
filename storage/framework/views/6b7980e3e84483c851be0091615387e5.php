
<table class="table table-bordered table-hover align-middle mb-0" border="1">
    <thead class="table-light">
        <tr>
            <th rowspan="3" class="text-center" style="width:40px;">No</th>
            <th rowspan="3">Mata Pelajaran</th>
            <?php $__currentLoopData = $tingkatList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th colspan="3" class="text-center">Jenjang Kelas <?php echo e($t->nama ?? $t->kode); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <th rowspan="3" class="text-center">Total Jam<br>(Seluruh Tingkat)</th>
            <th rowspan="3" class="text-center">Kebutuhan Guru<br>(<?php echo e($beban); ?> JP)</th>
            <th colspan="4" class="text-center">Existing (Guru Saat Ini)</th>
            <th rowspan="3" class="text-center">Lebih</th>
            <th rowspan="3" class="text-center">Kurang</th>
            <th rowspan="3" class="text-center">Status</th>
        </tr>
        <tr>
            <?php $__currentLoopData = $tingkatList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th colspan="3" class="text-center">Alokasi</th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <th rowspan="2" class="text-center">PNS</th>
            <th rowspan="2" class="text-center">PPPK</th>
            <th rowspan="2" class="text-center">KKI</th>
            <th rowspan="2" class="text-center">Hon</th>
        </tr>
        <tr>
            <?php $__currentLoopData = $tingkatList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th class="text-center">Jam</th>
                <th class="text-center">Kls</th>
                <th class="text-center">Total</th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tr>
    </thead>
    <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $hasil; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td class="text-center"><?php echo e($loop->iteration); ?></td>
            <td><?php echo e($h['mapel']->nama_mapel); ?></td>
            <?php $__currentLoopData = $tingkatList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td class="text-center"><?php echo e($h['per_tingkat'][$t->id]['jam']); ?></td>
                <td class="text-center"><?php echo e($h['per_tingkat'][$t->id]['kls']); ?></td>
                <td class="text-center fw-semibold"><?php echo e($h['per_tingkat'][$t->id]['total']); ?></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <td class="text-center fw-semibold"><?php echo e($h['total_jam']); ?></td>
            <td class="text-center fw-semibold"><?php echo e($h['kebutuhan']); ?></td>
            <td class="text-center"><?php echo e($h['existing']['pns']); ?></td>
            <td class="text-center"><?php echo e($h['existing']['pppk']); ?></td>
            <td class="text-center"><?php echo e($h['existing']['kki']); ?></td>
            <td class="text-center"><?php echo e($h['existing']['hon']); ?></td>
            <td class="text-center text-success fw-semibold"><?php echo e($h['lebih'] ?: ''); ?></td>
            <td class="text-center text-danger fw-semibold"><?php echo e($h['kurang'] ?: ''); ?></td>
            <td class="text-center">
                <?php if($h['kurang'] > 0): ?>
                    <span class="badge bg-danger">Kurang</span>
                <?php elseif($h['lebih'] > 0): ?>
                    <span class="badge bg-info">Lebih</span>
                <?php else: ?>
                    <span class="badge bg-success">Cukup</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="<?php echo e(9 + count($tingkatList) * 3); ?>" class="text-center text-muted py-4">
                Belum ada data Alokasi Jam Mapel untuk tahun ajaran aktif. Isi dulu menu Alokasi Jam Mapel.
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
<?php /**PATH C:\laragon\www\kurikulum\resources\views/kurikulum/partials/analisis-guru-tabel.blade.php ENDPATH**/ ?>