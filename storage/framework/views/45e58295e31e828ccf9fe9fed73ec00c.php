<?php $__env->startSection('title', 'Modul Ajar Digital'); ?>
<?php $__env->startSection('content'); ?>


<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <span class="fw-semibold"><i class="bi bi-magic me-2"></i>Generator Modul Ajar Digital</span>
    </div>
    <div class="card-body">
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('kurikulum.modul-ajar.store')); ?>" id="form-modul">
            <?php echo csrf_field(); ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                    <select name="mata_pelajaran_id" id="mapel" class="form-select" required>
                        <option value="">-- Pilih Mapel --</option>
                        <?php $__currentLoopData = $mapelList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($m->id); ?>" <?php echo e((string) old('mata_pelajaran_id') === (string) $m->id ? 'selected' : ''); ?>><?php echo e($m->nama_mapel); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                    <select name="tingkat_kelas_id" id="tingkat" class="form-select" required>
                        <option value="">-- Pilih Tingkat --</option>
                        <?php $__currentLoopData = $tingkatList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t->id); ?>" <?php echo e((string) old('tingkat_kelas_id') === (string) $t->id ? 'selected' : ''); ?>><?php echo e($t->nama ?? $t->kode); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Jumlah Jam <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_jam" id="jumlah_jam" class="form-control" min="1" value="<?php echo e(old('jumlah_jam', 2)); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pertemuan Ke <span class="text-danger">*</span></label>
                    <input type="number" name="pertemuan_ke" id="pertemuan_ke" class="form-control" min="1" value="<?php echo e(old('pertemuan_ke', 1)); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                    <select name="semester" class="form-select" required>
                        <option value="">-- Pilih Semester --</option>
                        <?php $__currentLoopData = ['Ganjil', 'Genap']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $smt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($smt); ?>" <?php echo e(old('semester') === $smt ? 'selected' : ''); ?>><?php echo e($smt); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                    <select name="tahun_ajaran" class="form-select" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        <?php $__currentLoopData = $tahunList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ta->nama_tahun_ajaran); ?>"
                                <?php echo e(old('tahun_ajaran', optional($tahunAktif)->nama_tahun_ajaran) === $ta->nama_tahun_ajaran ? 'selected' : ''); ?>>
                                <?php echo e($ta->nama_tahun_ajaran); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">1. Pilih Capaian Pembelajaran (CP)</label>
                    <select id="pilih-cp" class="form-select">
                        <option value="">-- Pilih CP --</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">2. Pilih Tujuan Pembelajaran (TP)</label>
                    <select id="pilih-tp" class="form-select">
                        <option value="">-- Pilih TP --</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">3. Pilih ATP</label>
                    <select name="pemetaan_cp_tp_atp_id" id="pilih-atp" class="form-select">
                        <option value="">-- Pilih ATP --</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="border-start border-4 border-success bg-light rounded p-3">
                        <div class="fw-semibold text-success mb-1">Review ATP</div>
                        <div id="review-atp" class="text-muted fst-italic">ATP yang dipilih akan tampil di sini...</div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Judul Materi <span class="text-danger">*</span></label>
                    <input type="text" name="judul" id="judul" class="form-control" maxlength="150" value="<?php echo e(old('judul')); ?>" required>
                </div>

                <div class="col-12 d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">Kegiatan Pembelajaran</span>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-generate">
                        <i class="bi bi-stars me-1"></i>Generate Modul Ajar
                    </button>
                </div>

                <?php $__currentLoopData = App\Models\ModulAjar::BAGIAN_ISI; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($name === 'kegiatan_pembelajaran') continue; ?>
                    <div class="col-12">
                        <label class="form-label"><?php echo e($label); ?></label>
                        <textarea name="<?php echo e($name); ?>" id="<?php echo e($name); ?>" class="form-control editor-html" rows="4"><?php echo e(old($name)); ?></textarea>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div class="col-12"><span class="fw-semibold">LKPD (Lembar Kerja Peserta Didik)</span></div>
                <?php $__currentLoopData = App\Models\ModulAjar::BAGIAN_LKPD; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-12">
                        <label class="form-label">LKPD - <?php echo e($label); ?></label>
                        <textarea name="<?php echo e($name); ?>" id="<?php echo e($name); ?>" class="form-control editor-html" rows="4"><?php echo e(old($name)); ?></textarea>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save me-1"></i>Simpan Modul
                    </button>
                    <button type="submit" name="download_pdf" value="1" class="btn btn-danger">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Simpan &amp; Download PDF
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>


<div class="card shadow-sm">
    <div class="card-header bg-white">
        <span class="fw-semibold"><i class="bi bi-journal-text me-2"></i>Daftar Modul Ajar</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama Guru</th>
                    <th>Mapel</th>
                    <th>Kelas</th>
                    <th class="text-center">Pertemuan Ke</th>
                    <th>Semester</th>
                    <th>Tahun Ajaran</th>
                    <th>Judul</th>
                    <th style="width:190px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e(optional($item->pegawai)->nama ?? '-'); ?></td>
                    <td><?php echo e(optional($item->mataPelajaran)->nama_mapel ?? '-'); ?></td>
                    <td><?php echo e(optional($item->tingkatKelas)->nama ?? optional($item->tingkatKelas)->kode ?? '-'); ?></td>
                    <td class="text-center"><?php echo e($item->pertemuan_ke ?? '-'); ?></td>
                    <td><?php echo e($item->semester ?? '-'); ?></td>
                    <td><?php echo e($item->tahun_ajaran ?? '-'); ?></td>
                    <td><?php echo e($item->judul); ?></td>
                    <td>
                        <a href="<?php echo e(route('kurikulum.modul-ajar.pdf', $item->id)); ?>" class="btn btn-sm btn-danger" title="Download PDF">PDF</a>
                        <a href="<?php echo e(route('kurikulum.modul-ajar.word', $item->id)); ?>" class="btn btn-sm btn-success" title="Download Word">Word</a>
                        <a href="<?php echo e(route('kurikulum.modul-ajar.lkpd', $item->id)); ?>" class="btn btn-sm btn-info text-white" title="Download LKPD">LKPD</a>
                        <form action="<?php echo e(route('kurikulum.modul-ajar.destroy', $item->id)); ?>" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus modul ajar ini?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger" title="Hapus">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Belum ada modul ajar.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<?php echo $__env->make('partials.tinymce', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<script>
    // Data Pemetaan CP-TP-ATP untuk dropdown bertingkat CP -> TP -> ATP.
    var PEMETAAN = <?php echo $pemetaanJson; ?>;

    // ============ Bantu baca/tulis isi textarea (TinyMCE atau polos) ============
    function editor(id) {
        return (window.tinymce && tinymce.get(id)) || null;
    }

    function ambilIsi(id) {
        var ed = editor(id);
        return ed ? ed.getContent({ format: 'text' }) : ($('#' + id).val() || '');
    }

    function tulisIsi(id, teks) {
        var ed = editor(id);
        if (ed) {
            ed.setContent(keHtml(teks));
        } else {
            $('#' + id).val(teks);
        }
    }

    function tulisHtml(id, html) {
        var ed = editor(id);
        if (ed) {
            ed.setContent(html || '');
        } else {
            $('#' + id).val(html || '');
        }
    }

    /** Teks hasil generate (per baris) diubah jadi paragraf HTML untuk editor. */
    function keHtml(teks) {
        return (teks || '').split('\n').map(function (baris) {
            var aman = baris.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return '<p>' + (aman.trim() === '' ? '&nbsp;' : aman) + '</p>';
        }).join('');
    }

    function potong(teks, batas) {
        teks = (teks || '').replace(/\s+/g, ' ').trim();
        return teks.length > batas ? teks.slice(0, batas) + '...' : teks;
    }

    function barisPemetaanTerfilter() {
        var mapel = $('#mapel').val(), tingkat = $('#tingkat').val();
        return PEMETAAN.filter(function (p) {
            return (!mapel || String(p.mata_pelajaran_id) === String(mapel))
                && (!tingkat || String(p.tingkat_kelas_id) === String(tingkat));
        });
    }

    function isiDropdown($sel, placeholder, opsi) {
        $sel.empty().append($('<option>').val('').text(placeholder));
        opsi.forEach(function (o) {
            $sel.append($('<option>').val(o.value).text(o.text));
        });
        $sel.val('').trigger('change.select2');
    }

    function muatCp() {
        var unik = {};
        barisPemetaanTerfilter().forEach(function (p) {
            if (p.capaian_pembelajaran && !unik[p.capaian_pembelajaran]) {
                unik[p.capaian_pembelajaran] = true;
            }
        });
        isiDropdown($('#pilih-cp'), '-- Pilih CP --', Object.keys(unik).map(function (cp) {
            return { value: cp, text: potong(cp, 140) };
        }));
        muatTp();
    }

    function muatTp() {
        var cp = $('#pilih-cp').val();
        var baris = barisPemetaanTerfilter().filter(function (p) { return !cp || p.capaian_pembelajaran === cp; });
        var unik = {};
        baris.forEach(function (p) {
            if (p.tujuan_pembelajaran && !unik[p.tujuan_pembelajaran]) unik[p.tujuan_pembelajaran] = true;
        });
        isiDropdown($('#pilih-tp'), '-- Pilih TP --', cp ? Object.keys(unik).map(function (tp) {
            return { value: tp, text: potong(tp, 140) };
        }) : []);
        muatAtp();
    }

    function muatAtp() {
        var cp = $('#pilih-cp').val(), tp = $('#pilih-tp').val();
        var baris = barisPemetaanTerfilter().filter(function (p) {
            return (!cp || p.capaian_pembelajaran === cp) && (!tp || p.tujuan_pembelajaran === tp);
        });
        isiDropdown($('#pilih-atp'), '-- Pilih ATP --', (cp && tp) ? baris.map(function (p) {
            return { value: p.id, text: potong(p.alur_tujuan_pembelajaran, 140) };
        }) : []);
        tampilkanReview();
    }

    function tampilkanReview() {
        var id = $('#pilih-atp').val();
        var baris = PEMETAAN.find(function (p) { return String(p.id) === String(id); });
        if (baris) {
            $('#review-atp').removeClass('fst-italic').text(baris.alur_tujuan_pembelajaran);
        } else {
            $('#review-atp').addClass('fst-italic').text('ATP yang dipilih akan tampil di sini...');
        }
    }

    // ============ Kerangka modul bawaan (dipakai bila AI tidak tersedia) ============
    function generateLokal(diamDiam) {
        var judul = $('#judul').val() || '(judul materi)';
        var mapel = $('#mapel option:selected').text() || '(mapel)';
        var tingkat = $('#tingkat option:selected').text() || '(tingkat)';
        var jp = $('#jumlah_jam').val() || '2';
        var atpId = $('#pilih-atp').val();
        var pemetaan = PEMETAAN.find(function (p) { return String(p.id) === String(atpId); });
        var tp = pemetaan ? pemetaan.tujuan_pembelajaran : ($('#pilih-tp').val() || '(pilih TP terlebih dahulu)');

        var isi = {
            target_peserta_didik:
                '1. Peserta didik reguler/tipikal kelas ' + tingkat + '.\n' +
                '2. Peserta didik dengan kesulitan belajar: mendapatkan pendampingan khusus dan penyederhanaan materi.\n' +
                '3. Peserta didik dengan pencapaian tinggi: mendapatkan pengayaan berupa soal/aktivitas tingkat lanjut.',
            model_pembelajaran:
                'Model: Problem Based Learning (PBL).\n' +
                'Pendekatan: Saintifik.\n' +
                'Metode: Diskusi kelompok, tanya jawab, presentasi, dan penugasan.\n' +
                'Moda: Tatap muka (' + jp + ' JP).',
            pertanyaan_pemantik:
                '1. Apa yang kalian ketahui tentang ' + judul + '?\n' +
                '2. Di mana kalian pernah menjumpai ' + judul + ' dalam kehidupan sehari-hari?\n' +
                '3. Mengapa ' + judul + ' penting untuk dipelajari?',
            pendahuluan:
                '1. Guru membuka pembelajaran dengan salam dan doa bersama.\n' +
                '2. Guru memeriksa kehadiran dan kesiapan peserta didik.\n' +
                '3. Guru menyampaikan tujuan pembelajaran: ' + potong(tp, 200) + '\n' +
                '4. Guru memberikan apersepsi dan pertanyaan pemantik terkait materi ' + judul + '.\n' +
                '5. Guru menyampaikan langkah kegiatan dan teknik penilaian.',
            kegiatan_inti:
                '1. Orientasi masalah: peserta didik mengamati permasalahan kontekstual terkait ' + judul + '.\n' +
                '2. Mengorganisasi: peserta didik dibagi menjadi beberapa kelompok kecil.\n' +
                '3. Membimbing penyelidikan: setiap kelompok mengumpulkan informasi dan berdiskusi menyelesaikan LKPD tentang ' + judul + '.\n' +
                '4. Mengembangkan hasil karya: kelompok menyusun dan mempresentasikan hasil diskusinya.\n' +
                '5. Menganalisis dan evaluasi: guru bersama peserta didik menanggapi presentasi dan meluruskan miskonsepsi.',
            penutup:
                '1. Guru bersama peserta didik menyimpulkan pembelajaran tentang ' + judul + '.\n' +
                '2. Guru memberikan refleksi dan umpan balik terhadap proses pembelajaran.\n' +
                '3. Guru menyampaikan rencana materi pertemuan berikutnya.\n' +
                '4. Pembelajaran ditutup dengan doa dan salam.',
            asesmen_diagnostik:
                'Asesmen non-kognitif: menanyakan kondisi dan kesiapan belajar peserta didik.\n' +
                'Asesmen kognitif: tanya jawab awal untuk memetakan pemahaman awal peserta didik tentang ' + judul + '.',
            asesmen_formatif:
                'Penilaian proses selama pembelajaran: keaktifan diskusi, hasil pengerjaan LKPD, dan presentasi kelompok (observasi dengan rubrik).',
            asesmen_sumatif:
                'Tes tertulis (uraian/pilihan ganda) di akhir lingkup materi ' + judul + ' sesuai tujuan pembelajaran.',
            lkpd_tujuan:
                'Setelah menyelesaikan LKPD ini, peserta didik mampu:\n' + potong(tp, 300),
            lkpd_petunjuk:
                '1. Berdoalah sebelum mengerjakan.\n' +
                '2. Tulis nama anggota kelompok pada kolom yang tersedia.\n' +
                '3. Baca setiap langkah kegiatan dengan teliti, lalu diskusikan bersama kelompokmu.\n' +
                '4. Tanyakan kepada guru bila ada langkah yang belum dipahami.',
            lkpd_kegiatan:
                '1. Amati permasalahan tentang ' + judul + ' yang diberikan guru.\n' +
                '2. Diskusikan bersama kelompok dan tuliskan hasil pengamatanmu.\n' +
                '3. Selesaikan soal-soal latihan tentang ' + judul + '.\n' +
                '4. Presentasikan hasil kerja kelompokmu di depan kelas.',
            lkpd_refleksi:
                '1. Apa yang sudah kamu pahami dari materi ' + judul + '?\n' +
                '2. Bagian mana yang masih terasa sulit?\n' +
                '3. Apa yang akan kamu lakukan agar lebih memahami materi ini?',
            lkpd_asesmen:
                'Kerjakan soal berikut secara mandiri:\n' +
                '1. (Soal pemahaman konsep tentang ' + judul + ')\n' +
                '2. (Soal penerapan dalam kehidupan sehari-hari)\n' +
                '3. (Soal penalaran/HOTS)'
        };

        if (! diamDiam && ! konfirmasiTimpa()) {
            return;
        }
        Object.keys(isi).forEach(function (k) { tulisIsi(k, isi[k]); });
    }

    // ============ Generate isi modul lewat AI (Claude) ============
    function konfirmasiTimpa() {
        var $editor = $('#form-modul textarea.editor-html');
        var adaIsi = $editor.toArray().some(function (el) { return ambilIsi(el.id).trim() !== ''; });

        return ! adaIsi || confirm('Beberapa bagian sudah terisi. Timpa dengan hasil generate?');
    }

    function generateModul() {
        if (! $('#mapel').val() || ! $('#tingkat').val()) {
            alert('Pilih Mata Pelajaran dan Tingkat terlebih dahulu.');
            return;
        }
        if (! $('#judul').val().trim()) {
            alert('Isi Judul Materi terlebih dahulu supaya isi modul sesuai materinya.');
            $('#judul').trigger('focus');
            return;
        }
        if (! konfirmasiTimpa()) {
            return;
        }

        var $tombol = $('#btn-generate');
        var labelAsli = $tombol.html();
        $tombol.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyusun modul dengan AI...');

        $.ajax({
            url: '<?php echo e(route('kurikulum.modul-ajar.generate')); ?>',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('#form-modul input[name="_token"]').val() },
            data: {
                mata_pelajaran_id: $('#mapel').val(),
                tingkat_kelas_id: $('#tingkat').val(),
                judul: $('#judul').val(),
                jumlah_jam: $('#jumlah_jam').val(),
                pertemuan_ke: $('#pertemuan_ke').val(),
                semester: $('[name="semester"]').val(),
                pemetaan_cp_tp_atp_id: $('#pilih-atp').val(),
                capaian_pembelajaran: $('#pilih-cp').val(),
                tujuan_pembelajaran: $('#pilih-tp').val()
            }
        }).done(function (res) {
            if (res && res.tersedia === false) {
                // Kunci API belum diatur — pakai kerangka bawaan aplikasi.
                generateLokal(true);
                alert(res.pesan);
                return;
            }
            Object.keys(res.isi || {}).forEach(function (k) { tulisHtml(k, res.isi[k]); });
        }).fail(function (xhr) {
            var pesan = (xhr.responseJSON && (xhr.responseJSON.pesan || xhr.responseJSON.message))
                || 'Gagal menghubungi layanan AI.';
            if (confirm(pesan + '\n\nPakai kerangka modul bawaan aplikasi sebagai gantinya?')) {
                generateLokal(true);
            }
        }).always(function () {
            $tombol.prop('disabled', false).html(labelAsli);
        });
    }
    $(function () {
        $('#mapel, #tingkat').on('change', muatCp);
        $('#pilih-cp').on('change', muatTp);
        $('#pilih-tp').on('change', muatAtp);
        $('#pilih-atp').on('change', tampilkanReview);
        $('#btn-generate').on('click', generateModul);
        muatCp();
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\kurikulum\resources\views/kurikulum/modul-ajar.blade.php ENDPATH**/ ?>