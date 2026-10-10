<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($bagian === 'lkpd' ? 'LKPD' : 'Modul Ajar'); ?> - <?php echo e($modul->judul); ?></title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #000; margin: 24px; }
        h1 { font-size: 16px; text-align: center; margin: 0 0 2px; text-transform: uppercase; }
        h2 { font-size: 13px; text-align: center; margin: 0 0 16px; font-weight: normal; }
        table.identitas { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.identitas td { border: 1px solid #333; padding: 5px 8px; vertical-align: top; }
        table.identitas td.label { width: 28%; font-weight: bold; background: #f0f0f0; }
        .bagian { margin-bottom: 12px; }
        .bagian .judul-bagian { font-weight: bold; background: #f0f0f0; border: 1px solid #333; padding: 5px 8px; }
        .bagian .isi-bagian { border: 1px solid #333; border-top: 0; padding: 6px 8px; }
        .bagian .isi-bagian p { margin: 0 0 6px; }
        .bagian .isi-bagian ol, .bagian .isi-bagian ul { margin: 0 0 6px; padding-left: 20px; }
        .bagian .isi-bagian table { border-collapse: collapse; width: 100%; }
        .bagian .isi-bagian table td, .bagian .isi-bagian table th { border: 1px solid #333; padding: 4px 6px; }
        .ttd { width: 100%; margin-top: 28px; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <h1><?php echo e($bagian === 'lkpd' ? 'Lembar Kerja Peserta Didik (LKPD)' : 'Modul Ajar'); ?></h1>
    <h2><?php echo e($modul->judul); ?></h2>

    <table class="identitas">
        <tr>
            <td class="label">Penyusun</td>
            <td><?php echo e(optional($modul->pegawai)->nama ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label">Mata Pelajaran</td>
            <td><?php echo e(optional($modul->mataPelajaran)->nama_mapel ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label">Kelas / Tingkat</td>
            <td><?php echo e(optional($modul->tingkatKelas)->nama ?? optional($modul->tingkatKelas)->kode ?? '-'); ?><?php echo e($modul->fase ? ' (Fase '.$modul->fase.')' : ''); ?></td>
        </tr>
        <tr>
            <td class="label">Semester / Tahun Ajaran</td>
            <td><?php echo e($modul->semester ?? '-'); ?> / <?php echo e($modul->tahun_ajaran ?? '-'); ?></td>
        </tr>
        <tr>
            <td class="label">Pertemuan Ke / Alokasi Waktu</td>
            <td><?php echo e($modul->pertemuan_ke ?? '-'); ?> / <?php echo e($modul->jumlah_jam ? $modul->jumlah_jam.' JP' : '-'); ?></td>
        </tr>
    </table>

    <?php if($bagian === 'lkpd'): ?>
        <?php $__currentLoopData = App\Models\ModulAjar::BAGIAN_LKPD; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(filled($modul->{$name})): ?>
            <div class="bagian">
                <div class="judul-bagian"><?php echo e($label); ?></div>
                <div class="isi-bagian"><?php echo App\Models\ModulAjar::htmlIsi($modul->{$name}); ?></div>
            </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        
        <?php if(filled($modul->capaian_pembelajaran)): ?>
        <div class="bagian">
            <div class="judul-bagian">Capaian Pembelajaran (CP)</div>
            <div class="isi-bagian"><?php echo App\Models\ModulAjar::htmlIsi($modul->capaian_pembelajaran); ?></div>
        </div>
        <?php endif; ?>
        <?php if(filled($modul->tujuan_pembelajaran)): ?>
        <div class="bagian">
            <div class="judul-bagian">Tujuan Pembelajaran (TP)</div>
            <div class="isi-bagian"><?php echo App\Models\ModulAjar::htmlIsi($modul->tujuan_pembelajaran); ?></div>
        </div>
        <?php endif; ?>
        <?php if(filled($modul->alur_tujuan_pembelajaran)): ?>
        <div class="bagian">
            <div class="judul-bagian">Alur Tujuan Pembelajaran (ATP)</div>
            <div class="isi-bagian"><?php echo App\Models\ModulAjar::htmlIsi($modul->alur_tujuan_pembelajaran); ?></div>
        </div>
        <?php endif; ?>

        <?php $__currentLoopData = App\Models\ModulAjar::BAGIAN_ISI; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(filled($modul->{$name})): ?>
            <div class="bagian">
                <div class="judul-bagian"><?php echo e($label); ?></div>
                <div class="isi-bagian"><?php echo App\Models\ModulAjar::htmlIsi($modul->{$name}); ?></div>
            </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <table class="ttd">
        <tr>
            <td>
                Mengetahui,<br>Kepala Sekolah<br><br><br><br>
                ..............................
            </td>
            <td>
                Guru Mata Pelajaran<br><br><br><br><br>
                <?php echo e(optional($modul->pegawai)->nama ?? '..............................'); ?>

            </td>
        </tr>
    </table>

    <?php if($autoPrint): ?>
    <script>window.addEventListener('load', function () { window.print(); });</script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\laragon\www\kurikulum\resources\views/kurikulum/modul-ajar-cetak.blade.php ENDPATH**/ ?>