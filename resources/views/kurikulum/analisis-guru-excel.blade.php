{{-- View khusus export Excel: kop laporan + tabel analisis. --}}
<table>
    <tr><td colspan="5"><strong>ANALISIS KEBUTUHAN GURU</strong></td></tr>
    <tr><td colspan="5"><strong>{{ optional($identitas)->nama_sekolah ?? '-' }}</strong></td></tr>
    <tr><td colspan="5">NPSN: {{ optional($identitas)->npsn ?? '-' }} | Tahun Ajaran: {{ optional($tahunAktif)->nama_tahun_ajaran ?? '-' }} | Beban Standar: {{ $beban }} JP/Minggu</td></tr>
    <tr><td colspan="5"></td></tr>
</table>
@include('kurikulum.partials.analisis-guru-tabel')
