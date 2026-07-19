{{-- Tabel Analisis Kebutuhan Guru. Dipakai halaman web dan export Excel. --}}
<table class="table table-bordered table-hover align-middle mb-0" border="1">
    <thead class="table-light">
        <tr>
            <th rowspan="3" class="text-center" style="width:40px;">No</th>
            <th rowspan="3">Mata Pelajaran</th>
            @foreach($tingkatList as $t)
                <th colspan="3" class="text-center">Jenjang Kelas {{ $t->nama ?? $t->kode }}</th>
            @endforeach
            <th rowspan="3" class="text-center">Total Jam<br>(Seluruh Tingkat)</th>
            <th rowspan="3" class="text-center">Kebutuhan Guru<br>({{ $beban }} JP)</th>
            <th colspan="4" class="text-center">Existing (Guru Saat Ini)</th>
            <th rowspan="3" class="text-center">Lebih</th>
            <th rowspan="3" class="text-center">Kurang</th>
            <th rowspan="3" class="text-center">Status</th>
        </tr>
        <tr>
            @foreach($tingkatList as $t)
                <th colspan="3" class="text-center">Alokasi</th>
            @endforeach
            <th rowspan="2" class="text-center">PNS</th>
            <th rowspan="2" class="text-center">PPPK</th>
            <th rowspan="2" class="text-center">KKI</th>
            <th rowspan="2" class="text-center">Hon</th>
        </tr>
        <tr>
            @foreach($tingkatList as $t)
                <th class="text-center">Jam</th>
                <th class="text-center">Kls</th>
                <th class="text-center">Total</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($hasil as $h)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ $h['mapel']->nama_mapel }}</td>
            @foreach($tingkatList as $t)
                <td class="text-center">{{ $h['per_tingkat'][$t->id]['jam'] }}</td>
                <td class="text-center">{{ $h['per_tingkat'][$t->id]['kls'] }}</td>
                <td class="text-center fw-semibold">{{ $h['per_tingkat'][$t->id]['total'] }}</td>
            @endforeach
            <td class="text-center fw-semibold">{{ $h['total_jam'] }}</td>
            <td class="text-center fw-semibold">{{ $h['kebutuhan'] }}</td>
            <td class="text-center">{{ $h['existing']['pns'] }}</td>
            <td class="text-center">{{ $h['existing']['pppk'] }}</td>
            <td class="text-center">{{ $h['existing']['kki'] }}</td>
            <td class="text-center">{{ $h['existing']['hon'] }}</td>
            <td class="text-center text-success fw-semibold">{{ $h['lebih'] ?: '' }}</td>
            <td class="text-center text-danger fw-semibold">{{ $h['kurang'] ?: '' }}</td>
            <td class="text-center">
                @if($h['kurang'] > 0)
                    <span class="badge bg-danger">Kurang</span>
                @elseif($h['lebih'] > 0)
                    <span class="badge bg-info">Lebih</span>
                @else
                    <span class="badge bg-success">Cukup</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="{{ 9 + count($tingkatList) * 3 }}" class="text-center text-muted py-4">
                Belum ada data Alokasi Jam Mapel untuk tahun ajaran aktif. Isi dulu menu Alokasi Jam Mapel.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
