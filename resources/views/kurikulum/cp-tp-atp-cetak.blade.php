<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemetaan Kompetensi</title>
    <style>
        @page { size: A4 landscape; margin: 1cm; mso-page-orientation: landscape; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 8px; color: #000; margin: 14px; }
        .sekolah { font-size: 14px; font-weight: bold; text-align: center; margin: 0; text-transform: uppercase; }
        .garis { border-bottom: 2px solid #C00000; margin: 4px 0 8px; }
        .judul { font-size: 10px; font-weight: bold; text-align: center; margin: 0 0 3px; text-transform: uppercase; }
        .rincian { font-size: 7px; font-weight: bold; text-align: center; color: #C55A11; text-transform: uppercase; margin: 0; }
        .info { font-size: 7px; text-align: center; color: #555; margin: 4px 0 0; font-style: italic; }
        .mapel { font-size: 10px; font-weight: bold; text-transform: uppercase; margin: 12px 0 4px; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.data th, table.data td { border: 1px solid #C00000; padding: 3px 4px; vertical-align: top; }
        table.data th { background: #C00000; color: #FFF; text-align: center; font-weight: bold; }
        table.data td { color: #1F3864; }
        td.tengah { text-align: center; color: #000; }
        td p { margin: 0 0 4px; }
        td ol, td ul { margin: 0 0 4px; padding-left: 14px; }
        td table { border-collapse: collapse; width: 100%; }
        td table td, td table th { border: 1px solid #C00000; padding: 2px 3px; }
        td.no { text-align: center; font-weight: bold; color: #C00000; }
        td.kosong { text-align: center; vertical-align: middle; color: #1F3864; }
        .nihil { text-align: center; padding: 14px; color: #555; }
    </style>
</head>
<body>
    <div class="sekolah">{{ optional($identitas)->nama_sekolah ?? '-' }}</div>
    <div class="garis"></div>
    <div class="judul">Pemetaan Kompetensi</div>
    <div class="rincian">
        Analisis Capaian Pembelajaran, Tujuan Pembelajaran, Alur Tujuan Pembelajaran, KKTP,
        Jenis Pembelajaran, Sumber Belajar, Karakter, Kelas/Semester
    </div>
    @php
        $ket = collect();
        if (! empty($tahunAjaran ?? '')) {
            $ket->push('Tahun Pelajaran: '.$tahunAjaran);
        }
        foreach (($filterAktif ?? []) as $judul => $nilai) {
            $ket->push($judul.': '.$nilai);
        }
        if (! empty($kataKunci ?? '')) {
            $ket->push('Kata kunci: "'.$kataKunci.'"');
        }
        // Data sudah diurutkan per mata pelajaran di controller; di sini hanya
        // dikelompokkan supaya tiap mapel punya judul dan penomoran sendiri.
        $perMapel = $items->groupBy(fn ($p) => optional($p->mataPelajaran)->nama_mapel ?: 'Tanpa Mata Pelajaran');
    @endphp
    @if($ket->isNotEmpty())
        <div class="info">{{ $ket->implode(' | ') }}</div>
    @endif

    @forelse($perMapel as $namaMapel => $baris)
        <div class="mapel">Mata Pelajaran: {{ $namaMapel }}</div>
        <table class="data">
            <thead>
                <tr>
                    <th style="width:3%;">No.</th>
                    <th style="width:11%;">Elemen</th>
                    <th style="width:27%;">Capaian Pembelajaran</th>
                    <th style="width:13%;">Tujuan Pembelajaran</th>
                    <th style="width:8%;">ATP</th>
                    <th style="width:12%;">Indikator KKTP</th>
                    <th style="width:8%;">Model</th>
                    <th style="width:7%;">Sumber</th>
                    <th style="width:7%;">Karakter</th>
                    <th style="width:6%;">Kelas/Sem</th>
                </tr>
            </thead>
            <tbody>
                @foreach($baris as $p)
                    @php
                        // Isi CP/TP/ATP/KKTP bisa berupa HTML dari editor atau
                        // teks polos (data lama & hasil import Excel).
                        $html = fn ($nilai) => App\Support\TeksKaya::html($nilai);
                        $tp = trim(App\Support\TeksKaya::polos($p->tujuan_pembelajaran));
                        $atp = trim(App\Support\TeksKaya::polos($p->alur_tujuan_pembelajaran));
                        $tingkat = optional($p->tingkatKelas)->nomor ?: optional($p->tingkatKelas)->nama;
                        $kelasSem = trim(($tingkat ?: '-').' / '.($p->semester ?: '-'));
                    @endphp
                    <tr>
                        <td class="no">{{ $loop->iteration }}</td>
                        <td>{!! $p->elemen ? $html($p->elemen) : '-' !!}</td>
                        <td>{!! $html($p->capaian_pembelajaran) !!}</td>
                        @if($tp === '' && $atp === '')
                            {{-- Baris yang baru berisi CP saja, mis. hasil import daftar CP. --}}
                            <td class="kosong" colspan="7">Belum ada TP/ATP</td>
                        @else
                            <td>{!! $html($p->tujuan_pembelajaran) !!}</td>
                            <td>{!! $html($p->alur_tujuan_pembelajaran) !!}</td>
                            <td>{!! $html($p->indikator_kktp) !!}</td>
                            <td>{{ implode(', ', (array) $p->model_pembelajaran) }}</td>
                            <td>{{ implode(', ', (array) $p->sumber_belajar) }}</td>
                            <td>{{ implode(', ', (array) $p->karakter_dpl) }}</td>
                            <td class="tengah">{{ $kelasSem }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <div class="nihil">Belum ada data pemetaan.</div>
    @endforelse

    @if(!empty($autoPrint))
    <script>window.addEventListener('load', function () { window.print(); });</script>
    @endif
</body>
</html>
