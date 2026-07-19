@extends('layouts.app')
@section('title', 'Identitas Sekolah')
@section('content')
<div class="card shadow-sm" style="max-width:760px;">
    <div class="card-header bg-white">Identitas Sekolah</div>
    <div class="card-body">
        <form method="POST" action="{{ route('setting.identitas.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @php
                $inputs = [
                    ['npsn', 'NPSN'],
                    ['nama_sekolah', 'Nama Sekolah'],
                    ['kelurahan', 'Kelurahan/Desa'],
                    ['kecamatan', 'Kecamatan'],
                    ['kabupaten', 'Kabupaten/Kota'],
                    ['provinsi', 'Provinsi'],
                    ['kode_pos', 'Kode Pos'],
                    ['telepon', 'Telepon'],
                    ['email', 'Email'],
                    ['website', 'Website'],
                    ['nama_kepala_sekolah', 'Nama Kepala Sekolah'],
                    ['nip_kepala_sekolah', 'NIP Kepala Sekolah'],
                ];
            @endphp

            <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" rows="2" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $identitas->alamat) }}</textarea>
                @error('alamat')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                @foreach($inputs as [$name, $label])
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ $label }}</label>
                        <input type="text" name="{{ $name }}" value="{{ old($name, $identitas->{$name}) }}" class="form-control @error($name) is-invalid @enderror">
                        @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                @endforeach
            </div>

            <div class="mb-3">
                <label class="form-label">Logo Sekolah</label>
                <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror">
                @if($identitas->logo)
                    <div class="form-text">Logo saat ini: <a href="{{ Storage::url($identitas->logo) }}" target="_blank">lihat</a></div>
                @endif
                @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
        </form>
    </div>
</div>
@endsection
