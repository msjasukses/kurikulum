<?php $__env->startSection('title', 'Alokasi Jam Mapel'); ?>
<?php $__env->startSection('content'); ?>

<?php ($isEdit = $editItem !== null); ?>


<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between">
        <span class="fw-semibold">
            <i class="bi <?php echo e($isEdit ? 'bi-pencil-square' : 'bi-plus-circle'); ?> me-2"></i><?php echo e($isEdit ? 'Edit Alokasi Jam' : 'Tambah Alokasi Jam Baru'); ?>

        </span>
        <?php if($tahunAktif): ?>
        <span class="badge bg-primary">Tahun Ajaran: <?php echo e($tahunAktif->nama_tahun_ajaran); ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="<?php echo e($isEdit ? route('kurikulum.alokasi-jam.update', $editItem->id) : route('kurikulum.alokasi-jam.store')); ?>">
            <?php echo csrf_field(); ?>
            <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tingkat Kelas <span class="text-danger">*</span></label>
                    <select name="tingkat_kelas_id" id="tingkat_kelas_id" class="form-select" required>
                        <option value="">Pilih Tingkat</option>
                        <?php $__currentLoopData = $tingkatList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->id); ?>" data-jumlah-kelas="<?php echo e($t->jumlah_rombel); ?>"
                                <?php echo e((string) old('tingkat_kelas_id', optional($editItem)->tingkat_kelas_id) === (string) $t->id ? 'selected' : ''); ?>>
                                <?php echo e($t->nama ?? $t->kode); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jumlah Kelas <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_kelas" id="jumlah_kelas" class="form-control" min="0" required
                           value="<?php echo e(old('jumlah_kelas', optional($editItem)->jumlah_kelas ?? 0)); ?>">
                    <div class="form-text">Terisi otomatis dari jumlah rombel.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                    <select name="mata_pelajaran_id" class="form-select" required>
                        <option value="">Pilih Mapel...</option>
                        <?php $__currentLoopData = $mapelList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->id); ?>"
                                <?php echo e((string) old('mata_pelajaran_id', optional($editItem)->mata_pelajaran_id) === (string) $m->id ? 'selected' : ''); ?>>
                                <?php echo e($m->nama_mapel); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jam (JP) / Minggu <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_jam_per_minggu" class="form-control" min="1" required
                           value="<?php echo e(old('jumlah_jam_per_minggu', optional($editItem)->jumlah_jam_per_minggu)); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                    <select name="semester" class="form-select" required>
                        <?php $__currentLoopData = ['Ganjil', 'Genap']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $smt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($smt); ?>" <?php echo e(old('semester', optional($editItem)->semester ?? 'Ganjil') === $smt ? 'selected' : ''); ?>><?php echo e($smt); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                    <select name="tahun_ajaran" class="form-select" required>
                        <option value="">Pilih Tahun Ajaran</option>
                        <?php $__currentLoopData = $tahunList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ta->nama_tahun_ajaran); ?>"
                                <?php echo e(old('tahun_ajaran', optional($editItem)->tahun_ajaran ?? optional($tahunAktif)->nama_tahun_ajaran) === $ta->nama_tahun_ajaran ? 'selected' : ''); ?>>
                                <?php echo e($ta->nama_tahun_ajaran); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-end gap-2">
                    <?php if($isEdit): ?>
                    <a href="<?php echo e(route('kurikulum.alokasi-jam.index', request()->except('edit'))); ?>" class="btn btn-outline-secondary">Batal</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i><?php echo e($isEdit ? 'Update Alokasi' : 'Simpan Alokasi'); ?>

                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <span class="fw-semibold"><i class="bi bi-journal-text me-2"></i>Daftar Alokasi Jam</span>
        <form method="GET" class="d-flex flex-wrap gap-2" id="filter-form">
            <select name="tahun" class="form-select form-select-sm w-auto js-filter">
                <option value="">Semua Tahun</option>
                <?php $__currentLoopData = $tahunList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($ta->nama_tahun_ajaran); ?>" <?php echo e($filterTahun === $ta->nama_tahun_ajaran ? 'selected' : ''); ?>><?php echo e($ta->nama_tahun_ajaran); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="semester" class="form-select form-select-sm w-auto js-filter">
                <option value="">Semua Semester</option>
                <?php $__currentLoopData = ['Ganjil', 'Genap']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $smt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($smt); ?>" <?php echo e($filterSemester === $smt ? 'selected' : ''); ?>><?php echo e($smt); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="tingkat" class="form-select form-select-sm w-auto js-filter">
                <option value="">Semua Tingkat</option>
                <?php $__currentLoopData = $tingkatList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($t->id); ?>" <?php echo e((string) $filterTingkat === (string) $t->id ? 'selected' : ''); ?>><?php echo e($t->nama ?? $t->kode); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:50px;">No.</th>
                    <th>Tingkat</th>
                    <th>Mapel</th>
                    <th class="text-center">Jam</th>
                    <th class="text-center">Kelas</th>
                    <th class="text-center">Total JP</th>
                    <th>Semester</th>
                    <th style="width:110px;" class="text-end">Opsi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e(optional($item->tingkatKelas)->nama ?? optional($item->tingkatKelas)->kode ?? '-'); ?></td>
                    <td><?php echo e(optional($item->mataPelajaran)->nama_mapel ?? '-'); ?></td>
                    <td class="text-center"><?php echo e($item->jumlah_jam_per_minggu); ?></td>
                    <td class="text-center"><?php echo e($item->jumlah_kelas); ?></td>
                    <td class="text-center fw-semibold"><?php echo e($item->total_jp); ?></td>
                    <td><?php echo e($item->semester); ?></td>
                    <td class="text-end">
                        <a href="<?php echo e(route('kurikulum.alokasi-jam.index', array_merge(request()->except('edit'), ['edit' => $item->id]))); ?>"
                           class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form action="<?php echo e(route('kurikulum.alokasi-jam.destroy', $item->id)); ?>" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus alokasi ini?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data untuk filter tersebut.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="5" class="text-end">TOTAL:</th>
                    <th class="text-center text-primary fs-5"><?php echo e($totalJp); ?> JP</th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    $(function () {
        // Isi otomatis "Jumlah Kelas" dari jumlah rombel tingkat terpilih.
        $('#tingkat_kelas_id').on('change', function () {
            var jumlah = $(this).find('option:selected').data('jumlah-kelas');
            $('#jumlah_kelas').val(jumlah !== undefined ? jumlah : 0);
        });

        // Filter daftar langsung diterapkan saat dropdown berubah.
        $('.js-filter').on('change', function () {
            $('#filter-form').trigger('submit');
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/kurikulum/alokasi-jam.blade.php ENDPATH**/ ?>