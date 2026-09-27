<?php $__env->startSection('title', 'Rekap Absensi per Siswa'); ?>
<?php $__env->startSection('content'); ?>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <form class="row g-2" method="GET">
            <?php if(!$isSiswa): ?>
            <div class="col-md-3">
                <label class="form-label">Siswa</label>
                <select name="siswa_id" class="form-select" required>
                    <option value="">-- Pilih Siswa --</option>
                    <?php $__currentLoopData = $siswaList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>" <?php if($siswaId == $s->id): echo 'selected'; endif; ?>><?php echo e($s->nama_siswa); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endif; ?>
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

<?php if($ringkasan->isNotEmpty()): ?>
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white"><i class="bi bi-bar-chart me-1"></i>Ringkasan per Mata Pelajaran</div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr><th>Mata Pelajaran</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>Total Pertemuan</th><th>Persentase Hadir</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $ringkasan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($persen = $r['total'] ? round($r['hadir'] / $r['total'] * 100) : 0); ?>
                <tr>
                    <td><?php echo e($r['nama']); ?></td>
                    <td><?php echo e($r['hadir']); ?></td>
                    <td><?php echo e($r['izin']); ?></td>
                    <td><?php echo e($r['sakit']); ?></td>
                    <td><?php echo e($r['alpa']); ?></td>
                    <td><?php echo e($r['total']); ?></td>
                    <td><span class="badge bg-<?php echo e($persen >= 75 ? 'success' : ($persen >= 50 ? 'warning text-dark' : 'danger')); ?>"><?php echo e($persen); ?>%</span></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if($records->isNotEmpty()): ?>
<div class="card shadow-sm">
    <div class="card-header bg-white"><i class="bi bi-list-ul me-1"></i>Rincian Kehadiran</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Tanggal</th><th>Mata Pelajaran</th><th>Status</th><th>Keterangan</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e(\Illuminate\Support\Carbon::parse($r->tanggal)->translatedFormat('d M Y')); ?></td>
                    <td><?php echo e(optional($r->mataPelajaran)->nama_mapel ?? '-'); ?></td>
                    <td><span class="badge bg-<?php echo e($r->status === 'Hadir' ? 'success' : ($r->status === 'Alpa' ? 'danger' : 'warning')); ?>"><?php echo e($r->status); ?></span></td>
                    <td><?php echo e($r->keterangan); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php elseif($siswaId): ?>
<div class="alert alert-info">Tidak ada data pada rentang tanggal tersebut.</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/absensi/rekap-siswa.blade.php ENDPATH**/ ?>