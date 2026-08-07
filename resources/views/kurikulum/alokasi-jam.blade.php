@extends('layouts.app')
@section('title', 'Alokasi Jam Mapel')
@section('content')

@php($isEdit = $editItem !== null)

{{-- ===================== Form Tambah / Edit Alokasi ===================== --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between">
        <span class="fw-semibold">
            <i class="bi {{ $isEdit ? 'bi-pencil-square' : 'bi-plus-circle' }} me-2"></i>{{ $isEdit ? 'Edit Alokasi Jam' : 'Tambah Alokasi Jam Baru' }}
        </span>
        @if($tahunAktif)
        <span class="badge bg-primary">Tahun Ajaran: {{ $tahunAktif->nama_tahun_ajaran }}</span>
        @endif
    </div>
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ $isEdit ? route('kurikulum.alokasi-jam.update', $editItem->id) : route('kurikulum.alokasi-jam.store') }}">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tingkat Kelas <span class="text-danger">*</span></label>
                    <select name="tingkat_kelas_id" id="tingkat_kelas_id" class="form-select" required>
                        <option value="">Pilih Tingkat</option>
                        @foreach($tingkatList as $t)
                            <option value="{{ $t->id }}" data-jumlah-kelas="{{ $t->jumlah_rombel }}"
                                {{ (string) old('tingkat_kelas_id', optional($editItem)->tingkat_kelas_id) === (string) $t->id ? 'selected' : '' }}>
                                {{ $t->nama ?? $t->kode }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jumlah Kelas <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_kelas" id="jumlah_kelas" class="form-control" min="0" required
                           value="{{ old('jumlah_kelas', optional($editItem)->jumlah_kelas ?? 0) }}">
                    <div class="form-text">Terisi otomatis dari jumlah rombel.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                    <select name="mata_pelajaran_id" class="form-select" required>
                        <option value="">Pilih Mapel...</option>
                        @foreach($mapelList as $m)
                            <option value="{{ $m->id }}"
                                {{ (string) old('mata_pelajaran_id', optional($editItem)->mata_pelajaran_id) === (string) $m->id ? 'selected' : '' }}>
                                {{ $m->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jam (JP) / Minggu <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah_jam_per_minggu" class="form-control" min="1" required
                           value="{{ old('jumlah_jam_per_minggu', optional($editItem)->jumlah_jam_per_minggu) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                    <select name="semester" class="form-select" required>
                        @foreach(['Ganjil', 'Genap'] as $smt)
                            <option value="{{ $smt }}" {{ old('semester', optional($editItem)->semester ?? 'Ganjil') === $smt ? 'selected' : '' }}>{{ $smt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                    <select name="tahun_ajaran" class="form-select" required>
                        <option value="">Pilih Tahun Ajaran</option>
                        @foreach($tahunList as $ta)
                            <option value="{{ $ta->nama_tahun_ajaran }}"
                                {{ old('tahun_ajaran', optional($editItem)->tahun_ajaran ?? optional($tahunAktif)->nama_tahun_ajaran) === $ta->nama_tahun_ajaran ? 'selected' : '' }}>
                                {{ $ta->nama_tahun_ajaran }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-end gap-2">
                    @if($isEdit)
                    <a href="{{ route('kurikulum.alokasi-jam.index', request()->except('edit')) }}" class="btn btn-outline-secondary">Batal</a>
                    @endif
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>{{ $isEdit ? 'Update Alokasi' : 'Simpan Alokasi' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ===================== Daftar Alokasi Jam ===================== --}}
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <span class="fw-semibold"><i class="bi bi-journal-text me-2"></i>Daftar Alokasi Jam</span>
        <form method="GET" class="d-flex flex-wrap gap-2" id="filter-form">
            <select name="tahun" class="form-select form-select-sm w-auto js-filter">
                <option value="">Semua Tahun</option>
                @foreach($tahunList as $ta)
                    <option value="{{ $ta->nama_tahun_ajaran }}" {{ $filterTahun === $ta->nama_tahun_ajaran ? 'selected' : '' }}>{{ $ta->nama_tahun_ajaran }}</option>
                @endforeach
            </select>
            <select name="semester" class="form-select form-select-sm w-auto js-filter">
                <option value="">Semua Semester</option>
                @foreach(['Ganjil', 'Genap'] as $smt)
                    <option value="{{ $smt }}" {{ $filterSemester === $smt ? 'selected' : '' }}>{{ $smt }}</option>
                @endforeach
            </select>
            <select name="tingkat" class="form-select form-select-sm w-auto js-filter">
                <option value="">Semua Tingkat</option>
                @foreach($tingkatList as $t)
                    <option value="{{ $t->id }}" {{ (string) $filterTingkat === (string) $t->id ? 'selected' : '' }}>{{ $t->nama ?? $t->kode }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:50px;">No.</th>
                    <th>Tingkat</th>
                    <th>Mapel</th>
                    <th class="text-center">Jam</th>
                    <th class="text-center">Kelas</th>
                    <th class="text-center">Total JP</th>
                    <th>Semester</th>
                    <th style="width:110px;" class="text-end">Opsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ optional($item->tingkatKelas)->nama ?? optional($item->tingkatKelas)->kode ?? '-' }}</td>
                    <td>{{ optional($item->mataPelajaran)->nama_mapel ?? '-' }}</td>
                    <td class="text-center">{{ $item->jumlah_jam_per_minggu }}</td>
                    <td class="text-center">{{ $item->jumlah_kelas }}</td>
                    <td class="text-center fw-semibold">{{ $item->total_jp }}</td>
                    <td>{{ $item->semester }}</td>
                    <td class="text-end">
                        <a href="{{ route('kurikulum.alokasi-jam.index', array_merge(request()->except('edit'), ['edit' => $item->id])) }}"
                           class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('kurikulum.alokasi-jam.destroy', $item->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Hapus alokasi ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data untuk filter tersebut.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="5" class="text-end">TOTAL:</th>
                    <th class="text-center text-primary fs-5">{{ $totalJp }} JP</th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        // Isi otomatis "Jumlah Kelas" dari jumlah rombel tingkat terpilih.
        $('#tingkat_kelas_id').on('change', function () {
            var jumlah = $(this).find('option:selected').data('jumlah-kelas');
            $('#jumlah_kelas').val(jumlah !== undefined ? jumlah : 0);
        });

        // Filter daftar langsung diterapkan saat dropdown berubah.
        $('.js-filter').on('change', function () {
            $('#filter-form').trigger('submit');
        });
    });
</script>
@endpush
@endsection
