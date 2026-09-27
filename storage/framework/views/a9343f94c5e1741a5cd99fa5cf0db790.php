<?php $__env->startSection('title', 'Kumpulkan Tugas'); ?>
<?php $__env->startSection('content'); ?>

<?php if(session('error')): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="fw-semibold"><i class="bi bi-journal-text me-2"></i><?php echo e($tugas->judul); ?></span>
        <?php if($tugas->tanggal_selesai): ?>
            <span class="badge <?php echo e($lewatBatas ? 'bg-danger' : 'bg-success'); ?>">
                Batas akhir: <?php echo e($tugas->tanggal_selesai->translatedFormat('d F Y')); ?>

                <?php echo e($lewatBatas ? '(sudah lewat)' : ''); ?>

            </span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Mata Pelajaran</dt>
            <dd class="col-sm-9"><?php echo e(optional($tugas->mataPelajaran)->nama_mapel ?? '-'); ?></dd>
            <dt class="col-sm-3">Kelas</dt>
            <dd class="col-sm-9"><?php echo e(optional($tugas->kelas)->nama_rombel ?? '-'); ?></dd>
            <dt class="col-sm-3">Guru</dt>
            <dd class="col-sm-9"><?php echo e(optional($tugas->pegawai)->nama_ptk ?? '-'); ?></dd>
            <?php if(filled($tugas->deskripsi)): ?>
            <dt class="col-sm-3">Instruksi</dt>
            <dd class="col-sm-9"><?php echo App\Support\TeksKaya::html($tugas->deskripsi); ?></dd>
            <?php endif; ?>
            <?php if($tugas->file_lampiran): ?>
            <dt class="col-sm-3">Lampiran Guru</dt>
            <dd class="col-sm-9"><a href="<?php echo e(Storage::url($tugas->file_lampiran)); ?>" target="_blank"><i class="bi bi-paperclip me-1"></i>Unduh lampiran</a></dd>
            <?php endif; ?>
        </dl>
    </div>
</div>

<?php if($pengumpulan && $pengumpulan->sudahDinilai()): ?>
<div class="card shadow-sm mb-3 border-success">
    <div class="card-header bg-white"><i class="bi bi-patch-check me-1 text-success"></i>Sudah Dinilai</div>
    <div class="card-body">
        <div class="fs-3 fw-bold text-success mb-2"><?php echo e($pengumpulan->nilai); ?></div>
        <?php if(filled($pengumpulan->umpan_balik)): ?>
            <div class="fw-semibold small">Umpan balik guru:</div>
            <p class="mb-0"><?php echo e($pengumpulan->umpan_balik); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <i class="bi bi-upload me-1"></i><?php echo e($pengumpulan ? 'Jawaban Saya' : 'Kumpulkan Jawaban'); ?>

        <?php if($pengumpulan && $pengumpulan->dikumpulkan_pada): ?>
            <span class="badge <?php echo e($pengumpulan->terlambat ? 'bg-warning text-dark' : 'bg-success'); ?> ms-1">
                Dikumpulkan <?php echo e($pengumpulan->dikumpulkan_pada->translatedFormat('d F Y H:i')); ?>

                <?php echo e($pengumpulan->terlambat ? '(terlambat)' : ''); ?>

            </span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if($pengumpulan && $pengumpulan->sudahDinilai()): ?>
            <p class="text-muted mb-3">Tugas sudah dinilai guru sehingga jawaban tidak bisa diubah lagi.</p>
            <?php if(filled($pengumpulan->catatan)): ?>
                <div class="fw-semibold small">Catatan yang dikirim:</div>
                <p><?php echo e($pengumpulan->catatan); ?></p>
            <?php endif; ?>
            <?php if($pengumpulan->file): ?>
                <a href="<?php echo e(Storage::url($pengumpulan->file)); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i>File yang dikirim
                </a>
            <?php endif; ?>
        <?php else: ?>
            <?php if($lewatBatas): ?>
                <div class="alert alert-warning py-2">
                    <i class="bi bi-clock-history me-1"></i>Batas waktu sudah lewat. Tugas tetap bisa dikumpulkan,
                    tetapi akan ditandai <strong>terlambat</strong>.
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('ruangbelajar.pengumpulan.store', $tugas->id)); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label" for="catatan">Catatan / Jawaban</label>
                    <textarea name="catatan" id="catatan" rows="5"
                              class="form-control <?php $__errorArgs = ['catatan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                              placeholder="Tulis jawaban atau keterangan singkat tentang tugasmu..."><?php echo e(old('catatan', $pengumpulan->catatan ?? '')); ?></textarea>
                    <?php $__errorArgs = ['catatan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="file">File Jawaban <span class="text-muted">(opsional)</span></label>
                    <input type="file" name="file" id="file" class="form-control <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <div class="form-text">
                        Maksimal 10 MB. Format: pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip, rar, jpg, png.
                        <?php if($pengumpulan && $pengumpulan->file): ?>
                            <br>File saat ini:
                            <a href="<?php echo e(Storage::url($pengumpulan->file)); ?>" target="_blank">lihat</a>
                            — mengunggah file baru akan menggantikannya.
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary">
                        <i class="bi bi-send me-1"></i><?php echo e($pengumpulan ? 'Perbarui Jawaban' : 'Kumpulkan Tugas'); ?>

                    </button>
                    <a href="<?php echo e(route('ruangbelajar.tugas.index')); ?>" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/ruangbelajar/pengumpulan-form.blade.php ENDPATH**/ ?>