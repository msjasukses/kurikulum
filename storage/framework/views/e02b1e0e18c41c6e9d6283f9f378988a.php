<?php $__env->startSection('title', ($item ? 'Edit' : 'Isi').' Agenda Mengajar'); ?>
<?php $__env->startSection('content'); ?>

<?php ($isEdit = $item !== null); ?>

<div class="card shadow-sm" style="max-width:760px;">
    <div class="card-header bg-white">
        <i class="bi bi-journal-check me-2"></i><?php echo e($isEdit ? 'Ubah' : 'Isi'); ?> Agenda Mengajar — Form Pengisian Jurnal Kelas
    </div>
    <div class="card-body">
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data"
                      action="<?php echo e($isEdit ? route('kurikulum.agenda.update', $item->id) : route('kurikulum.agenda.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <?php if($isEdit): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Waktu Pengisian Agenda</label>
                        <input type="text" class="form-control bg-light" readonly
                               value="<?php echo e(($isEdit ? $item->waktu_pengisian : now())->locale('id')->translatedFormat('l, d F Y \p\u\k\u\l H:i:s')); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Guru <span class="text-danger">*</span></label>
                        <select name="guru_id" id="guru_id" class="form-select" required>
                            <option value="">-- Pilih Guru --</option>
                            <?php $__currentLoopData = $guruList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($g->id); ?>" <?php if((string) old('guru_id', optional($item)->guru_id) === (string) $g->id): echo 'selected'; endif; ?>>
                                    <?php echo e($g->nama_ptk); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mengajar Sebagai</label>
                        <select name="mengajar_sebagai" class="form-select">
                            <option value="normal" <?php if(old('mengajar_sebagai', optional($item)->mengajar_sebagai ?? 'normal') === 'normal'): echo 'selected'; endif; ?>>Diri Sendiri (Jadwal Normal)</option>
                            <option value="pengganti" <?php if(old('mengajar_sebagai', optional($item)->mengajar_sebagai) === 'pengganti'): echo 'selected'; endif; ?>>Guru Pengganti</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih Jam Mengajar (Hari <?php echo e($hariIni); ?>)</label>
                        <select id="jadwal_hari_ini" class="form-select">
                            <option value="">-- Pilih dari jadwal hari ini --</option>
                            <?php $__currentLoopData = $jadwalHariIni; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($j->id); ?>"
                                        data-guru="<?php echo e($j->guru_id); ?>"
                                        data-jam-ke="<?php echo e(optional($j->jamMengajar)->jam_ke); ?>"
                                        data-kelas="<?php echo e($j->kelas_id); ?>"
                                        data-mapel="<?php echo e($j->mata_pelajaran_id); ?>">
                                    <?php echo e(optional($j->guru)->nama_ptk); ?> — <?php echo e(optional($j->jamMengajar)->label ?? 'Jam ?'); ?> — <?php echo e(optional($j->kelas)->nama_rombel); ?> — <?php echo e(optional($j->mataPelajaran)->nama_mapel); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php if($jadwalHariIni->isEmpty()): ?>
                            <div class="alert alert-warning py-2 mt-2 mb-0 small">Tidak ada jadwal ditemukan untuk hari ini. Isi jam, kelas, dan mapel secara manual.</div>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label">Jam Ke</label>
                            <input type="text" name="jam_ke" id="jam_ke" class="form-control" placeholder="mis. 1-3"
                                   value="<?php echo e(old('jam_ke', optional($item)->jam_ke)); ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jumlah Jam (JP) <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_jam" id="jumlah_jam" class="form-control" min="1" max="12" required
                                   value="<?php echo e(old('jumlah_jam', optional($item)->jumlah_jam ?? 1)); ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" id="kelas_id" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php $__currentLoopData = $kelasList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php ($tahunLain = $tahunAktif && $k->tahun_ajaran_id !== $tahunAktif->id); ?>
                                    <option value="<?php echo e($k->id); ?>" data-siswa="<?php echo e($siswaPerKelas[$k->id] ?? 0); ?>"
                                        <?php if((string) old('kelas_id', optional($item)->kelas_id) === (string) $k->id): echo 'selected'; endif; ?>>
                                        <?php echo e($k->nama_rombel); ?><?php if($tahunLain): ?> (T.A. <?php echo e(optional($k->tahunAjaran)->nama_tahun_ajaran); ?>)<?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Paralel</label>
                            <input type="text" name="paralel" class="form-control" placeholder="1"
                                   value="<?php echo e(old('paralel', optional($item)->paralel ?? '1')); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mata_pelajaran_id" id="mata_pelajaran_id" class="form-select" required>
                                <option value="">Terisi otomatis dari jam mengajar...</option>
                                <?php $__currentLoopData = $mapelList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($m->id); ?>" <?php if((string) old('mata_pelajaran_id', optional($item)->mata_pelajaran_id) === (string) $m->id): echo 'selected'; endif; ?>>
                                        <?php echo e($m->nama_mapel); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 text-center mb-2">
                        <div class="col-4">
                            <div class="border rounded p-2 bg-light">
                                <div class="small text-muted">Total Murid</div>
                                <input type="number" name="jumlah_siswa" id="jumlah_siswa" min="0" required
                                       class="form-control form-control-sm text-center fw-bold border-0 bg-light"
                                       value="<?php echo e(old('jumlah_siswa', optional($item)->jumlah_siswa ?? 0)); ?>">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 bg-light">
                                <div class="small text-muted">Hadir</div>
                                <div id="hadir" class="fw-bold text-success"><?php echo e(old('hadir', optional($item)->hadir ?? 0)); ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 bg-light">
                                <div class="small text-muted">Tidak Hadir</div>
                                <div id="absen" class="fw-bold text-danger"><?php echo e(old('absen', optional($item)->absen ?? 0)); ?></div>
                            </div>
                        </div>
                    </div>

                    <div id="rincian_status" class="mb-3 small text-muted"></div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Siswa (Kehadiran per Siswa)</label>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <button type="button" class="btn btn-sm btn-outline-success" id="tandai_hadir">
                                <i class="bi bi-check2-all me-1"></i>Tandai semua hadir
                            </button>
                        </div>
                        <div id="daftar_siswa" class="border rounded p-2" style="max-height:320px; overflow-y:auto;">
                            <div class="text-muted small">Pilih jam mengajar atau kelas dulu untuk menampilkan daftar siswa.</div>
                        </div>
                        <div class="form-text">
                            Status tiap siswa: <?php echo e(implode(', ', $statusKehadiran)); ?>. Jumlah Hadir/Tidak Hadir dihitung otomatis.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih Modul Ajar (Rencana Pembelajaran)</label>
                        <select name="modul_ajar_id" id="modul_ajar_id" class="form-select">
                            <option value="">-- Pilih Modul Ajar --</option>
                            <?php $__currentLoopData = $modulList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($mod->id); ?>" data-mapel="<?php echo e($mod->mata_pelajaran_id); ?>" data-judul="<?php echo e($mod->judul); ?>"
                                    <?php if((string) old('modul_ajar_id', optional($item)->modul_ajar_id) === (string) $mod->id): echo 'selected'; endif; ?>>
                                    <?php echo e($mod->judul); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <div class="form-text">Pilihan modul akan muncul setelah Anda memilih Jam Mengajar / mapel.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Materi Pembelajaran</label>
                        <textarea name="materi" id="materi" rows="2" class="form-control"
                                  placeholder="Akan terisi otomatis dari modul..."><?php echo e(old('materi', optional($item)->materi)); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan Guru</label>
                        <textarea name="catatan" rows="2" class="form-control"
                                  placeholder="Misal: Situasi tertib, kendala, dll."><?php echo e(old('catatan', optional($item)->catatan)); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Photo Kegiatan</label>

                        <div class="border border-2 border-dashed rounded p-3 text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <label class="btn btn-outline-primary mb-0">
                                    <i class="bi bi-camera me-1"></i>Kamera
                                    <input type="file" name="photo" id="photo_kamera" accept="image/*" capture="environment" hidden>
                                </label>
                                <label class="btn btn-outline-success mb-0">
                                    <i class="bi bi-image me-1"></i>Galeri
                                    <input type="file" id="photo_galeri" accept="image/*" hidden>
                                </label>
                            </div>
                            <div id="photo_nama" class="small text-muted mt-2">
                                <?php echo e($isEdit && $item->photo ? 'Photo tersimpan: '.basename($item->photo) : 'Belum ada file dipilih.'); ?>

                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Agenda</button>
                        <a href="<?php echo e(route('kurikulum.agenda.index')); ?>" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// Dijalankan setelah jQuery & Select2 siap (lihat layouts/app.blade.php),
// supaya tampilan dropdown ikut berubah saat diisi otomatis.
$(function () {
    var SISWA_PER_KELAS = <?php echo json_encode($siswaPerKelasNama, 15, 512) ?>;
    var STATUS = <?php echo json_encode($statusKehadiran, 15, 512) ?>;
    var WARNA = <?php echo json_encode(App\Models\AgendaMengajar::WARNA_KEHADIRAN, 15, 512) ?>;

    var $jadwal = $('#jadwal_hari_ini');
    var $guru = $('#guru_id');
    var $jamKe = $('#jam_ke');
    var $kelas = $('#kelas_id');
    var $mapel = $('#mata_pelajaran_id');
    var $jumlahSiswa = $('#jumlah_siswa');
    var $daftar = $('#daftar_siswa');
    var $rincian = $('#rincian_status');
    var $modul = $('#modul_ajar_id');
    var $materi = $('#materi');

    // Status tersimpan (mode ubah / input yang gagal validasi).
    <?php ($tersimpan = old('kehadiran', collect(optional($item)->kehadiran_siswa ?? [])->pluck('status', 'siswa_id')->all())); ?>
    var statusTersimpan = <?php echo json_encode((object) $tersimpan, 15, 512) ?>;

    function aman(teks) {
        return $('<div>').text(teks == null ? '' : teks).html();
    }

    /** Isi nilai select lalu segarkan tampilan Select2-nya. */
    function isiSelect($el, nilai) {
        if (nilai === undefined || nilai === null || nilai === '') {
            return;
        }
        $el.val(String(nilai)).trigger('change.select2');
    }

    function hitung() {
        var jumlah = {};
        STATUS.forEach(function (st) { jumlah[st] = 0; });

        var $status = $daftar.find('select.js-status');
        $status.each(function () {
            var nilai = $(this).val();
            if (jumlah[nilai] !== undefined) { jumlah[nilai]++; }
        });

        if ($status.length) {
            $jumlahSiswa.val($status.length);
            $('#hadir').text(jumlah['Hadir']);
            $('#absen').text($status.length - jumlah['Hadir']);
        }

        $rincian.html(STATUS.filter(function (st) { return st !== 'Hadir' && jumlah[st] > 0; })
            .map(function (st) {
                return '<span class="badge bg-' + (WARNA[st] || 'secondary') + ' me-1">' + st + ': ' + jumlah[st] + '</span>';
            }).join(''));
    }

    function tampilkanSiswa() {
        var daftar = SISWA_PER_KELAS[$kelas.val()] || [];

        if (! $kelas.val() || ! daftar.length) {
            $daftar.html('<div class="text-muted small">' +
                ($kelas.val() ? 'Belum ada data siswa untuk kelas ini.' : 'Pilih jam mengajar atau kelas dulu untuk menampilkan daftar siswa.') +
                '</div>');
            $rincian.empty();
            return;
        }

        $daftar.html(daftar.map(function (siswa, i) {
            var status = statusTersimpan[siswa.id] || 'Hadir';
            var opsi = STATUS.map(function (st) {
                return '<option value="' + st + '"' + (st === status ? ' selected' : '') + '>' + st + '</option>';
            }).join('');

            return '<div class="d-flex align-items-center gap-2 py-1' + (i ? ' border-top' : '') + '">' +
                   '<span class="small flex-grow-1">' + (i + 1) + '. ' + aman(siswa.nama) + '</span>' +
                   '<input type="hidden" name="kehadiran_nama[' + siswa.id + ']" value="' + aman(siswa.nama) + '">' +
                   '<select name="kehadiran[' + siswa.id + ']" class="form-select form-select-sm js-status" style="width:auto;">' + opsi + '</select>' +
                   '</div>';
        }).join(''));

        $daftar.find('select.js-status').on('change', hitung);
        hitung();
    }

    /** Daftar jadwal disaring mengikuti guru yang dipilih. */
    function filterJadwal() {
        $jadwal.find('option').each(function () {
            if (! this.value) { return; }
            this.hidden = !! $guru.val() && this.dataset.guru !== $guru.val();
        });
    }

    // Pilih jam mengajar -> isi otomatis guru, jam ke, kelas, mapel, dan daftar siswa.
    $jadwal.on('change', function () {
        var opt = this.selectedOptions[0];
        if (! opt || ! opt.value) { return; }

        if (opt.dataset.guru && ! $guru.val()) { isiSelect($guru, opt.dataset.guru); }
        if (opt.dataset.jamKe) { $jamKe.val(opt.dataset.jamKe); }
        isiSelect($kelas, opt.dataset.kelas);
        isiSelect($mapel, opt.dataset.mapel);

        tampilkanSiswa();
    });

    $guru.on('change', filterJadwal);
    $kelas.on('change', tampilkanSiswa);

    $('#tandai_hadir').on('click', function () {
        $daftar.find('select.js-status').val('Hadir');
        hitung();
    });

    $modul.on('change', function () {
        var opt = this.selectedOptions[0];
        if (opt && opt.dataset.judul && ! $materi.val().trim()) { $materi.val(opt.dataset.judul); }
    });

    // Photo: dua tombol (Kamera/Galeri) memakai satu name="photo" — sinkronkan.
    var fotoKamera = document.getElementById('photo_kamera');
    var fotoGaleri = document.getElementById('photo_galeri');
    var fotoNama = document.getElementById('photo_nama');
    fotoGaleri.addEventListener('change', function () {
        if (fotoGaleri.files.length) {
            fotoKamera.files = fotoGaleri.files;
            fotoNama.textContent = fotoGaleri.files[0].name;
        }
    });
    fotoKamera.addEventListener('change', function () {
        if (fotoKamera.files.length) { fotoNama.textContent = fotoKamera.files[0].name; }
    });

    filterJadwal();
    tampilkanSiswa();
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/kurikulum/agenda-mengajar/form.blade.php ENDPATH**/ ?>