<?php $__env->startSection('title', 'Pengumpulan Tugas'); ?>
<?php $__env->startSection('content'); ?>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="fw-semibold"><i class="bi bi-journal-check me-2"></i><?php echo e($tugas->judul); ?></span>
        <span>
            <span class="badge bg-primary"><?php echo e($jumlahKumpul); ?> dari <?php echo e($jumlahSiswa); ?> siswa mengumpulkan</span>
            <?php if($tugas->tanggal_selesai): ?>
                <span class="badge bg-secondary">Batas: <?php echo e($tugas->tanggal_selesai->translatedFormat('d F Y')); ?></span>
            <?php endif; ?>
        </span>
    </div>
    <div class="card-body py-2">
        <span class="text-muted small">
            <?php echo e(optional($tugas->mataPelajaran)->nama_mapel ?? '-'); ?> &middot;
            Kelas <?php echo e(optional($tugas->kelas)->nama_rombel ?? '-'); ?> &middot;
            <?php echo e(optional($tugas->pegawai)->nama_ptk ?? '-'); ?>

        </span>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Nama Siswa</th>
                    <th>Status</th>
                    <th>Jawaban</th>
                    <th style="width:280px;">Penilaian</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $baris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php ($p = $b['pengumpulan']); ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td>
                        <?php echo e($b['siswa']->nama_siswa); ?>

                        <div class="text-muted small">NISN: <?php echo e($b['siswa']->nisn ?: '-'); ?></div>
                    </td>
                    <td>
                        <?php if(! $p): ?>
                            <span class="badge bg-secondary">Belum mengumpulkan</span>
                        <?php else: ?>
                            <span class="badge <?php echo e($p->terlambat ? 'bg-warning text-dark' : 'bg-success'); ?>">
                                <?php echo e($p->terlambat ? 'Terlambat' : 'Tepat waktu'); ?>

                            </span>
                            <div class="text-muted small"><?php echo e(optional($p->dikumpulkan_pada)->translatedFormat('d M Y H:i')); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($p): ?>
                            <?php if(filled($p->catatan)): ?>
                                <div class="small"><?php echo e(Illuminate\Support\Str::limit($p->catatan, 120)); ?></div>
                            <?php endif; ?>
                            <?php if($p->file): ?>
                                <a href="<?php echo e(Storage::url($p->file)); ?>" target="_blank" class="small">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i>Unduh file
                                </a>
                            <?php endif; ?>
                            <?php if(blank($p->catatan) && ! $p->file): ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted small">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($p): ?>
                            <form method="POST" action="<?php echo e(route('ruangbelajar.pengumpulan.nilai', $p->id)); ?>" class="d-flex flex-column gap-1">
                                <?php echo csrf_field(); ?>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Nilai</span>
                                    <input type="number" name="nilai" min="0" max="100" value="<?php echo e($p->nilai); ?>" class="form-control" placeholder="0-100">
                                    <button class="btn btn-outline-primary" title="Simpan penilaian"><i class="bi bi-save"></i></button>
                                </div>
                                <input type="text" name="umpan_balik" value="<?php echo e($p->umpan_balik); ?>"
                                       class="form-control form-control-sm" maxlength="2000" placeholder="Umpan balik (opsional)">
                                <?php if($p->dinilai_pada): ?>
                                    <span class="text-muted small">Dinilai <?php echo e($p->dinilai_pada->translatedFormat('d M Y H:i')); ?></span>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <span class="text-muted small">Menunggu pengumpulan</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada siswa terdaftar di kelas ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        <a href="<?php echo e(route('ruangbelajar.tugas.index')); ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Tugas
        </a>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/ruangbelajar/pengumpulan-daftar.blade.php ENDPATH**/ ?>