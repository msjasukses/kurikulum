@extends('layouts.app')
@section('title', 'Pengumpulan Tugas')
@section('content')

<div class="card shadow-sm mb-3">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="fw-semibold"><i class="bi bi-journal-check me-2"></i>{{ $tugas->judul }}</span>
        <span>
            <span class="badge bg-primary">{{ $jumlahKumpul }} dari {{ $jumlahSiswa }} siswa mengumpulkan</span>
            @if($tugas->tanggal_selesai)
                <span class="badge bg-secondary">Batas: {{ $tugas->tanggal_selesai->translatedFormat('d F Y') }}</span>
            @endif
        </span>
    </div>
    <div class="card-body py-2">
        <span class="text-muted small">
            {{ optional($tugas->mataPelajaran)->nama_mapel ?? '-' }} &middot;
            Kelas {{ optional($tugas->kelas)->nama_rombel ?? '-' }} &middot;
            {{ optional($tugas->pegawai)->nama_ptk ?? '-' }}
        </span>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Nama Siswa</th>
                    <th>Status</th>
                    <th>Jawaban</th>
                    <th style="width:280px;">Penilaian</th>
                </tr>
            </thead>
            <tbody>
                @forelse($baris as $b)
                @php($p = $b['pengumpulan'])
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $b['siswa']->nama_siswa }}
                        <div class="text-muted small">NISN: {{ $b['siswa']->nisn ?: '-' }}</div>
                    </td>
                    <td>
                        @if(! $p)
                            <span class="badge bg-secondary">Belum mengumpulkan</span>
                        @else
                            <span class="badge {{ $p->terlambat ? 'bg-warning text-dark' : 'bg-success' }}">
                                {{ $p->terlambat ? 'Terlambat' : 'Tepat waktu' }}
                            </span>
                            <div class="text-muted small">{{ optional($p->dikumpulkan_pada)->translatedFormat('d M Y H:i') }}</div>
                        @endif
                    </td>
                    <td>
                        @if($p)
                            @if(filled($p->catatan))
                                <div class="small">{{ Illuminate\Support\Str::limit($p->catatan, 120) }}</div>
                            @endif
                            @if($p->file)
                                <a href="{{ Storage::url($p->file) }}" target="_blank" class="small">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i>Unduh file
                                </a>
                            @endif
                            @if(blank($p->catatan) && ! $p->file)
                                <span class="text-muted small">-</span>
                            @endif
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        @if($p)
                            <form method="POST" action="{{ route('ruangbelajar.pengumpulan.nilai', $p->id) }}" class="d-flex flex-column gap-1">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">Nilai</span>
                                    <input type="number" name="nilai" min="0" max="100" value="{{ $p->nilai }}" class="form-control" placeholder="0-100">
                                    <button class="btn btn-outline-primary" title="Simpan penilaian"><i class="bi bi-save"></i></button>
                                </div>
                                <input type="text" name="umpan_balik" value="{{ $p->umpan_balik }}"
                                       class="form-control form-control-sm" maxlength="2000" placeholder="Umpan balik (opsional)">
                                @if($p->dinilai_pada)
                                    <span class="text-muted small">Dinilai {{ $p->dinilai_pada->translatedFormat('d M Y H:i') }}</span>
                                @endif
                            </form>
                        @else
                            <span class="text-muted small">Menunggu pengumpulan</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada siswa terdaftar di kelas ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        <a href="{{ route('ruangbelajar.tugas.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Tugas
        </a>
    </div>
</div>

@endsection
