<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemetaan CP-TP-ATP</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #000; margin: 24px; }
        h1 { font-size: 15px; text-align: center; margin: 0 0 2px; text-transform: uppercase; }
        h2 { font-size: 12px; text-align: center; margin: 0 0 4px; font-weight: normal; }
        .sub { text-align: center; font-size: 11px; margin-bottom: 14px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #333; padding: 5px 6px; vertical-align: top; }
        table.data th { background: #f0f0f0; text-align: center; }
        td.tengah { text-align: center; white-space: nowrap; }
        td.isi { white-space: pre-wrap; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <h1>Pemetaan CP - TP - ATP</h1>
    <h2>{{ optional($identitas)->nama_sekolah ?? '-' }}</h2>
    <div class="sub">
        NPSN: {{ optional($identitas)->npsn ?? '-' }}
        &nbsp;|&nbsp; Dicetak: {{ now()->locale('id')->translatedFormat('d F Y') }}
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width:28px;">No</th>
                <th>Mata Pelajaran</th>
                <th>Tingkat</th>
                <th style="width:38px;">Fase</th>
                <th style="width:24%;">Capaian Pembelajaran (CP)</th>
                <th style="width:24%;">Tujuan Pembelajaran (TP)</th>
                <th style="width:24%;">Alur Tujuan Pembelajaran (ATP)</th>
                <th>Tahun Ajaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $p)
            <tr>
                <td class="tengah">{{ $loop->iteration }}</td>
                <td>{{ optional($p->mataPelajaran)->nama_mapel ?? '-' }}</td>
                <td class="tengah">{{ optional($p->tingkatKelas)->nama ?? optional($p->tingkatKelas)->kode ?? '-' }}</td>
                <td class="tengah">{{ $p->fase ?: '-' }}</td>
                <td class="isi">{{ $p->capaian_pembelajaran }}</td>
                <td class="isi">{{ $p->tujuan_pembelajaran }}</td>
                <td class="isi">{{ $p->alur_tujuan_pembelajaran }}</td>
                <td class="tengah">{{ $p->tahun_ajaran ?: '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center; padding:16px;">Belum ada data pemetaan.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($autoPrint))
    <script>window.addEventListener('load', function () { window.print(); });</script>
    @endif
</body>
</html>
