<?php $__env->startSection('title', 'Setting Profil'); ?>
<?php $__env->startSection('content'); ?>

<?php
    $roleLabel = [
        'admin' => 'Administrator',
        'guru' => 'Guru',
        'siswa' => 'Siswa',
    ][$user->role] ?? ucfirst($user->role);

    $hakAkses = [
        'admin' => ['Seluruh menu Master Data, Kepegawaian, Kesiswaan', 'Kurikulum, Ruang Belajar, Absensi (rekap & koreksi)', 'Manajemen User (admin, guru, siswa)'],
        'guru' => ['Kurikulum: jadwal, CP-TP-ATP, modul ajar, agenda mengajar', 'Ruang Belajar: materi online & tugas', 'Absensi: rekap kelas & koreksi'],
        'siswa' => ['Ruang Belajar: materi online & tugas', 'Absensi: rekap kehadiran pribadi', 'Kesiswaan: data orang tua (lihat saja)'],
    ][$user->role] ?? [];
?>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:88px;height:88px;font-size:2rem;">
                    <?php echo e(strtoupper(mb_substr($user->name, 0, 1))); ?>

                </div>
                <h5 class="mb-1"><?php echo e($user->name); ?></h5>
                <div class="text-muted small mb-2"><?php echo e($user->email); ?></div>
                <span class="badge bg-secondary text-uppercase"><?php echo e($roleLabel); ?></span>
                <span class="badge <?php echo e($user->aktif ? 'bg-success' : 'bg-danger'); ?>"><?php echo e($user->aktif ? 'Aktif' : 'Nonaktif'); ?></span>
            </div>
            <?php if($hakAkses): ?>
            <div class="card-footer bg-white">
                <div class="fw-semibold small mb-2"><i class="bi bi-shield-check me-1"></i>Hak Akses</div>
                <ul class="small text-muted mb-0 ps-3">
                    <?php $__currentLoopData = $hakAkses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $akses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($akses); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white"><i class="bi bi-person-lines-fill me-1"></i>Data Diri</div>
            <div class="card-body">
                <div class="row">
                    <?php $__currentLoopData = $detail; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $nilai]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-6 mb-3">
                            <div class="text-muted small"><?php echo e($label); ?></div>
                            <div><?php echo e($nilai ?: '-'); ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if (! ($akunLokal)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Data diri Anda bersumber dari aplikasi <strong>Datacenter</strong> dan bersifat lihat saja.
                        Perubahan data dilakukan di aplikasi Datacenter, lalu otomatis tersinkron saat Anda login kembali.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if($akunLokal): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white"><i class="bi bi-person-gear me-1"></i>Akun Login</div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('profil.update')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white"><i class="bi bi-key me-1"></i>Ganti Kata Sandi</div>
            <div class="card-body">
                <form method="POST" action="<?php echo e(route('profil.password')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kata Sandi Lama</label>
                            <input type="password" name="password_lama" class="form-control <?php $__errorArgs = ['password_lama'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <?php $__errorArgs = ['password_lama'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kata Sandi Baru</label>
                            <input type="password" name="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ulangi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-shield-lock me-1"></i>Ubah Kata Sandi</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-header bg-white"><i class="bi bi-key me-1"></i>Kata Sandi</div>
            <div class="card-body">
                <p class="mb-0 text-muted">
                    Kata sandi Anda diverifikasi langsung ke aplikasi <strong>Datacenter</strong> memakai
                    <?php echo e($user->isSiswa() ? 'NISN' : 'NIP'); ?> dan kata sandi yang sama seperti login di sana.
                    Silakan ubah kata sandi lewat aplikasi Datacenter.
                </p>
            </div>
        </div>
        <?php endif; ?>

        <?php if($bolehAturAi): ?>
        
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span><i class="bi bi-stars me-1"></i>Kunci API AI (Generate Modul Ajar)</span>
                <?php if($user->punyaKunciAi()): ?>
                    <span class="badge bg-success">Sudah diatur</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Belum diatur</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Kunci ini dipakai tombol <strong>Generate Modul Ajar</strong> di menu Modul Ajar Digital,
                    dan hanya berlaku untuk akun Anda sendiri. Kunci disimpan dalam bentuk terenkripsi serta
                    tidak pernah ditampilkan kembali secara utuh.
                </p>

                <form method="POST" action="<?php echo e(route('profil.ai')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="ai_provider">Penyedia AI</label>
                            <select name="ai_provider" id="ai_provider" class="form-select <?php $__errorArgs = ['ai_provider'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__currentLoopData = $penyediaAi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kode => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($kode); ?>" <?php if(old('ai_provider', $user->ai_provider) === $kode): echo 'selected'; endif; ?>><?php echo e($info['label']); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['ai_provider'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div class="form-text">
                                Ambil kunci di:
                                <?php $__currentLoopData = $penyediaAi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kode => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="d-none js-alamat-kunci" data-penyedia="<?php echo e($kode); ?>">
                                        <a href="<?php echo e($info['alamat_kunci']); ?>" target="_blank" rel="noopener"><?php echo e($info['alamat_kunci']); ?></a>
                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="ai_api_key">Kunci API</label>
                            <div class="input-group">
                                <input type="password" name="ai_api_key" id="ai_api_key" autocomplete="off"
                                       class="form-control <?php $__errorArgs = ['ai_api_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       placeholder="<?php echo e($user->punyaKunciAi() ? 'Tersimpan: '.$user->petunjukKunciAi() : 'Tempelkan kunci API di sini'); ?>">
                                <button type="button" class="btn btn-outline-secondary" id="lihat-kunci-ai"
                                        aria-label="Tampilkan kunci" title="Tampilkan kunci">
                                    <i class="bi bi-eye" id="ikon-kunci-ai"></i>
                                </button>
                            </div>
                            <?php $__errorArgs = ['ai_api_key'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <?php if($user->punyaKunciAi()): ?>
                                <div class="form-text">Kosongkan bila tidak ingin mengganti kunci yang tersimpan.</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="ai_model">Model <span class="text-muted">(opsional)</span></label>
                            <input type="text" name="ai_model" id="ai_model" value="<?php echo e(old('ai_model', $user->ai_model)); ?>"
                                   class="form-control <?php $__errorArgs = ['ai_model'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['ai_model'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback d-block"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div class="form-text">
                                <?php $__currentLoopData = $penyediaAi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kode => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="d-none js-contoh-model" data-penyedia="<?php echo e($kode); ?>">
                                        Kosongkan untuk memakai <strong><?php echo e($info['model_bawaan']); ?></strong>.
                                        Pilihan lain: <?php echo e($info['contoh_model']); ?>.
                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Pengaturan AI</button>
                        <?php if($user->punyaKunciAi()): ?>
                            <button name="hapus_kunci" value="1" class="btn btn-outline-danger"
                                    onclick="return confirm('Hapus kunci API AI dari akun ini?')">
                                <i class="bi bi-trash me-1"></i>Hapus Kunci
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if($bolehAturAi): ?>
<?php $__env->startPush('scripts'); ?>
<script>
    // Petunjuk (alamat kunci & contoh model) mengikuti penyedia yang dipilih,
    // plus tombol mata untuk memeriksa kunci yang sedang diketik.
    (function () {
        var $penyedia = $('#ai_provider');

        function segarkanPetunjuk() {
            var kode = $penyedia.val();
            $('.js-alamat-kunci, .js-contoh-model').each(function () {
                $(this).toggleClass('d-none', $(this).data('penyedia') !== kode);
            });
        }

        $penyedia.on('change', segarkanPetunjuk);
        segarkanPetunjuk();

        $('#lihat-kunci-ai').on('click', function () {
            var $kunci = $('#ai_api_key');
            var tampil = $kunci.attr('type') === 'password';
            $kunci.attr('type', tampil ? 'text' : 'password');
            $('#ikon-kunci-ai').toggleClass('bi-eye', !tampil).toggleClass('bi-eye-slash', tampil);
            $(this).attr('aria-label', tampil ? 'Sembunyikan kunci' : 'Tampilkan kunci')
                   .attr('title', tampil ? 'Sembunyikan kunci' : 'Tampilkan kunci');
            $kunci.trigger('focus');
        });
    })();
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/profil/edit.blade.php ENDPATH**/ ?>