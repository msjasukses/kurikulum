<?php $__env->startSection('title', 'Analisis Kebutuhan Guru'); ?>
<?php $__env->startSection('content'); ?>


<div class="card shadow-sm mb-4 d-print-none">
    <div class="card-body">
        <form method="GET" class="d-flex flex-wrap align-items-end gap-2">
            <div>
                <label class="form-label mb-1">Beban (JP)</label>
                <div class="input-group" style="width:180px;">
                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                    <input type="number" name="beban" class="form-control" min="1" value="<?php echo e($beban); ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-arrow-repeat me-1"></i>Generate
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()" title="Cetak">
                <i class="bi bi-printer"></i>
            </button>
            <a href="<?php echo e(route('kurikulum.analisis-guru.excel', ['beban' => $beban])); ?>" class="btn btn-outline-success" title="Export Excel">
                <i class="bi bi-file-earmark-excel"></i>
            </a>
        </form>
    </div>
</div>


<div class="card shadow-sm mb-4">
    <div class="card-body text-center">
        <h5 class="fw-bold text-uppercase mb-1">Analisis Kebutuhan Guru</h5>
        <h6 class="fw-bold mb-2"><?php echo e(optional($identitas)->nama_sekolah ?? '-'); ?></h6>
        <div class="small text-muted">
            NPSN: <span class="badge bg-light text-dark border"><?php echo e(optional($identitas)->npsn ?? '-'); ?></span>
            | Wilayah: <span class="badge bg-light text-dark border"><?php echo e(optional($identitas)->kabupaten ?: (optional($identitas)->kecamatan ?: '-')); ?></span>
            | Tahun Ajaran: <span class="badge bg-light text-dark border"><?php echo e(optional($tahunAktif)->nama_tahun_ajaran ?? '-'); ?></span>
            | Beban Standar: <span class="badge bg-light text-dark border"><?php echo e($beban); ?> JP/Minggu</span>
        </div>
    </div>
</div>


<div class="card shadow-sm">
    <div class="table-responsive">
        <?php echo $__env->make('kurikulum.partials.analisis-guru-tabel', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
</div>

<style>
    @media print {
        .sidebar, .topbar, .d-print-none { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        body { background: #fff; }
        .card { border: 0; box-shadow: none !important; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/kurikulum/analisis-guru.blade.php ENDPATH**/ ?>