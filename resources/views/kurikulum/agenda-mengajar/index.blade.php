@extends('layouts.app')
@section('title', 'Agenda Mengajar Guru')
@section('content')

<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <h5 class="mb-0 fw-semibold text-primary">Agenda Mengajar Guru</h5>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-success btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Cetak Jurnal
        </button>
        <a href="{{ route('kurikulum.agenda.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Isi Agenda Mengajar
        </a>
        <form method="GET" class="d-flex" role="search">
            <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm me-1" placeholder="Cari...">
            <button class="btn btn-outline-primary btn-sm"><i class="bi bi-search"></i></button>
        </form>
    </div>
</div>

@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif

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
            @forelse($items as $a)
                <tr>
                    <td class="text-center text-nowrap">
                        <a href="{{ route('kurikulum.agenda.edit', $a->id) }}" class="btn btn-sm btn-success" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('kurikulum.agenda.destroy', $a->id) }}" class="d-inline"
                              onsubmit="return confirm('Hapus agenda ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                    <td class="text-center">
                        @php($badge = ['Disetujui' => 'bg-success', 'Ditolak' => 'bg-danger'][$a->status] ?? 'bg-warning text-dark')
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('kurikulum.agenda.status', $a->id) }}">
                                @csrf
                                <select name="status" class="form-select form-select-sm border-0 badge {{ $badge }}"
                                        style="width:auto; display:inline-block; cursor:pointer;"
                                        onchange="this.form.submit()">
                                    @foreach(['Sedang Ditinjau', 'Disetujui', 'Ditolak'] as $s)
                                        <option value="{{ $s }}" @selected($a->status === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @else
                            <span class="badge {{ $badge }}">{{ $a->status }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ optional($a->guru)->nip ?? '-' }}</td>
                    <td>{{ optional($a->guru)->nama_ptk ?? '-' }}</td>
                    <td>{{ $a->waktu_pengisian?->locale('id')->translatedFormat('l, d F Y H:i') }} WIB</td>
                    <td class="text-center">{{ $a->jam_ke ?: '-' }}</td>
                    <td class="text-center">{{ optional($a->kelas)->nama_rombel ?? '-' }}</td>
                    <td class="text-center">{{ $a->paralel ?: '-' }}</td>
                    <td>{{ optional($a->mataPelajaran)->nama_mapel ?? '-' }}</td>
                    <td>{{ $a->materi }}</td>
                    <td class="text-center">{{ $a->jumlah_siswa }}</td>
                    <td class="text-center">{{ $a->hadir }}</td>
                    <td class="text-center">{{ $a->absen }}</td>
                    <td style="max-width:220px;">{{ $a->siswa_absen }}</td>
                    <td class="text-center">
                        @if($a->photo)
                            <a href="{{ asset('storage/'.$a->photo) }}" target="_blank">
                                <img src="{{ asset('storage/'.$a->photo) }}" alt="Photo kegiatan" style="width:48px;height:36px;object-fit:cover;" class="rounded">
                            </a>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $a->catatan }}</td>
                </tr>
            @empty
                <tr><td colspan="16" class="text-center text-muted py-4">Belum ada agenda mengajar. Klik "Isi Agenda Mengajar" untuk menambah.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center">
        <small class="text-muted">Records: {{ $items->count() }} of {{ $items->total() }}</small>
        {{ $items->links() }}
    </div>
</div>

@endsection
