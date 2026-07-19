<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $bagian === 'lkpd' ? 'LKPD' : 'Modul Ajar' }} - {{ $modul->judul }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #000; margin: 24px; }
        h1 { font-size: 16px; text-align: center; margin: 0 0 2px; text-transform: uppercase; }
        h2 { font-size: 13px; text-align: center; margin: 0 0 16px; font-weight: normal; }
        table.identitas { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.identitas td { border: 1px solid #333; padding: 5px 8px; vertical-align: top; }
        table.identitas td.label { width: 28%; font-weight: bold; background: #f0f0f0; }
        .bagian { margin-bottom: 12px; }
        .bagian .judul-bagian { font-weight: bold; background: #f0f0f0; border: 1px solid #333; padding: 5px 8px; }
        .bagian .isi-bagian { border: 1px solid #333; border-top: 0; padding: 6px 8px; white-space: pre-wrap; }
        .ttd { width: 100%; margin-top: 28px; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <h1>{{ $bagian === 'lkpd' ? 'Lembar Kerja Peserta Didik (LKPD)' : 'Modul Ajar' }}</h1>
    <h2>{{ $modul->judul }}</h2>

    <table class="identitas">
        <tr>
            <td class="label">Penyusun</td>
            <td>{{ optional($modul->pegawai)->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Mata Pelajaran</td>
            <td>{{ optional($modul->mataPelajaran)->nama_mapel ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kelas / Tingkat</td>
            <td>{{ optional($modul->tingkatKelas)->nama ?? optional($modul->tingkatKelas)->kode ?? '-' }}{{ $modul->fase ? ' (Fase '.$modul->fase.')' : '' }}</td>
        </tr>
        <tr>
            <td class="label">Semester / Tahun Ajaran</td>
            <td>{{ $modul->semester ?? '-' }} / {{ $modul->tahun_ajaran ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pertemuan Ke / Alokasi Waktu</td>
            <td>{{ $modul->pertemuan_ke ?? '-' }} / {{ $modul->jumlah_jam ? $modul->jumlah_jam.' JP' : '-' }}</td>
        </tr>
    </table>

    @if($bagian === 'lkpd')
        @foreach(App\Models\ModulAjar::BAGIAN_LKPD as $name => $label)
            @if(filled($modul->{$name}))
            <div class="bagian">
                <div class="judul-bagian">{{ $label }}</div>
                <div class="isi-bagian">{{ $modul->{$name} }}</div>
            </div>
            @endif
        @endforeach
    @else
        @if(filled($modul->capaian_pembelajaran))
        <div class="bagian">
            <div class="judul-bagian">Capaian Pembelajaran (CP)</div>
            <div class="isi-bagian">{{ $modul->capaian_pembelajaran }}</div>
        </div>
        @endif
        @if(filled($modul->tujuan_pembelajaran))
        <div class="bagian">
            <div class="judul-bagian">Tujuan Pembelajaran (TP)</div>
            <div class="isi-bagian">{{ $modul->tujuan_pembelajaran }}</div>
        </div>
        @endif
        @if(filled($modul->alur_tujuan_pembelajaran))
        <div class="bagian">
            <div class="judul-bagian">Alur Tujuan Pembelajaran (ATP)</div>
            <div class="isi-bagian">{{ $modul->alur_tujuan_pembelajaran }}</div>
        </div>
        @endif

        @foreach(App\Models\ModulAjar::BAGIAN_ISI as $name => $label)
            @if(filled($modul->{$name}))
            <div class="bagian">
                <div class="judul-bagian">{{ $label }}</div>
                <div class="isi-bagian">{{ $modul->{$name} }}</div>
            </div>
            @endif
        @endforeach
    @endif

    <table class="ttd">
        <tr>
            <td>
                Mengetahui,<br>Kepala Sekolah<br><br><br><br>
                ..............................
            </td>
            <td>
                Guru Mata Pelajaran<br><br><br><br><br>
                {{ optional($modul->pegawai)->nama ?? '..............................' }}
            </td>
        </tr>
    </table>

    @if($autoPrint)
    <script>window.addEventListener('load', function () { window.print(); });</script>
    @endif
</body>
</html>
