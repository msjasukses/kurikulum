<?php $__env->startSection('title', 'Agenda Mengajar Guru'); ?>
<?php $__env->startSection('content'); ?>

<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <h5 class="mb-0 fw-semibold text-primary">Agenda Mengajar Guru</h5>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Cetak Jurnal
        </button>
        <a href="<?php echo e(route('kurikulum.agenda.create')); ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Isi Agenda Mengajar
        </a>
        <form method="GET" class="d-flex" role="search">
            <input type="text" name="q" value="<?php echo e($q); ?>" class="form-control form-control-sm me-1" placeholder="Cari...">
            <button class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

<?php if(session('success')): ?><div class="alert alert-success py-2"><?php echo e(session('success')); ?></div><?php endif; ?>
<?php if(session('error')): ?><div class="alert alert-danger py-2"><?php echo e(session('error')); ?></div><?php endif; ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0" style="font-size:.85rem;">
            <thead class="text-white" style="background:#3f51b5;">
                <tr class="text-center">
                    <th>Aksi</th>
                    <th>Validasi</th>
                    <th>NIP</th>
                    <th>Nama Guru</th>
                    <th>Hari, Tanggal, Jam</th>
                    <th>Jam Ke</th>
                    <th>Kelas</th>
                    <th>Paralel</th>
                    <th>Mapel</th>
                    <th>Materi</th>
                    <th>Jml Siswa</th>
                    <th>Hadir</th>
                    <th>Absen</th>
                    <th>Nama Siswa Absen</th>
                    <th>Photo</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="text-center text-nowrap">
                        <a href="<?php echo e(route('kurikulum.agenda.edit', $a->id)); ?>" class="btn btn-sm btn-success" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="<?php echo e(route('kurikulum.agenda.destroy', $a->id)); ?>" class="d-inline"
                              onsubmit="return confirm('Hapus agenda ini?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                    <td class="text-center">
                        <?php ($badge = ['Disetujui' => 'bg-success', 'Ditolak' => 'bg-danger'][$a->status] ?? 'bg-warning text-dark'); ?>
                        <?php if(auth()->user()->isAdmin()): ?>
                            <form method="POST" action="<?php echo e(route('kurikulum.agenda.status', $a->id)); ?>">
                                <?php echo csrf_field(); ?>
                                <select name="status" class="form-select form-select-sm border-0 badge <?php echo e($badge); ?>"
                                        style="width:auto; display:inline-block; cursor:pointer;"
                                        onchange="this.form.submit()">
                                    <?php $__currentLoopData = ['Sedang Ditinjau', 'Disetujui', 'Ditolak']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($s); ?>" <?php if($a->status === $s): echo 'selected'; endif; ?>><?php echo e($s); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </form>
                        <?php else: ?>
                            <span class="badge <?php echo e($badge); ?>"><?php echo e($a->status); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo e(optional($a->guru)->nip ?? '-'); ?></td>
                    <td><?php echo e(optional($a->guru)->nama_ptk ?? '-'); ?></td>
                    <td><?php echo e($a->waktu_pengisian?->locale('id')->translatedFormat('l, d F Y H:i')); ?> WIB</td>
                    <td class="text-center"><?php echo e($a->jam_ke ?: '-'); ?></td>
                    <td class="text-center"><?php echo e(optional($a->kelas)->nama_rombel ?? '-'); ?></td>
                    <td class="text-center"><?php echo e($a->paralel ?: '-'); ?></td>
                    <td><?php echo e(optional($a->mataPelajaran)->nama_mapel ?? '-'); ?></td>
                    <td><?php echo e($a->materi); ?></td>
                    <td class="text-center"><?php echo e($a->jumlah_siswa); ?></td>
                    <td class="text-center"><?php echo e($a->hadir); ?></td>
                    <td class="text-center"><?php echo e($a->absen); ?></td>
                    <td style="max-width:220px;"><?php echo e($a->siswa_absen); ?></td>
                    <td class="text-center">
                        <?php if($a->photo): ?>
                            <a href="<?php echo e(asset('storage/'.$a->photo)); ?>" target="_blank">
                                <img src="<?php echo e(asset('storage/'.$a->photo)); ?>" alt="Photo kegiatan" style="width:48px;height:36px;object-fit:cover;" class="rounded">
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($a->catatan); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="16" class="text-center text-muted py-4">Belum ada agenda mengajar. Klik "Isi Agenda Mengajar" untuk menambah.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center">
        <small class="text-muted">Records: <?php echo e($items->count()); ?> of <?php echo e($items->total()); ?></small>
        <?php echo e($items->links()); ?>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/kurikulum/agenda-mengajar/index.blade.php ENDPATH**/ ?>