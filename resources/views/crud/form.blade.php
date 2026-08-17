@extends('layouts.app')
@section('title', $title)
@section('content')
@php
    $hasFile = collect($fields)->contains(fn($f) => $f['type'] === 'file');
    $pakaiEditor = collect($fields)->contains(fn($f) => !empty($f['editor']));
@endphp
<div class="card shadow-sm" style="max-width:{{ $pakaiEditor ? '1000px' : '760px' }};">
    <div class="card-header bg-white">
        {{ $item ? 'Ubah' : 'Tambah' }} {{ $title }}
    </div>
    <div class="card-body">
        <form method="POST"
              action="{{ $item ? route($routeName.'.update', $item->id) : route($routeName.'.store') }}"
              @if($hasFile) enctype="multipart/form-data" @endif>
            @csrf
            @if($item) @method('PUT') @endif

            @foreach($fields as $field)
                @php $name = $field['name']; $old = old($name, $item->{$name} ?? null); @endphp
                <div class="mb-3">
                    <label class="form-label">{{ $field['label'] }}</label>

                    @if(!empty($field['auto']))
                        {{-- Nilai diisi otomatis oleh sistem, jadi tidak ikut dikirim dari form. --}}
                        <input type="text" class="form-control" value="{{ $field['autoValue'] ?? $old }}" disabled>
                        <div class="form-text">Mengikuti tahun ajaran yang dipilih di kanan atas halaman.</div>

                    @elseif($field['type'] === 'textarea')
                        <textarea name="{{ $name }}" rows="{{ !empty($field['editor']) ? 6 : 3 }}"
                                  class="form-control @error($name) is-invalid @enderror @if(!empty($field['editor'])) editor-html @endif"
                                  placeholder="{{ $field['placeholder'] ?? '' }}">{{ $old }}</textarea>

                    @elseif($field['type'] === 'select')
                        <select name="{{ $name }}" class="form-select @error($name) is-invalid @enderror">
                            <option value="">-- Pilih {{ $field['label'] }} --</option>
                            @foreach(($field['options'] ?? []) as $value => $label)
                                <option value="{{ $value }}" @selected((string) $old === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>

                    @elseif($field['type'] === 'checkbox')
                        <div class="form-check">
                            <input type="hidden" name="{{ $name }}" value="0">
                            <input type="checkbox" name="{{ $name }}" value="1" class="form-check-input" @checked($old)>
                        </div>

                    @elseif($field['type'] === 'checkboxes')
                        @php $terpilih = array_map('strval', (array) old($name, $item->{$name} ?? [])); @endphp
                        <div class="border rounded p-2 @error($name) border-danger @enderror" style="max-height:220px; overflow-y:auto;">
                            @forelse(($field['options'] ?? []) as $value => $label)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="{{ $name }}-{{ $loop->index }}"
                                           name="{{ $name }}[]" value="{{ $value }}" @checked(in_array((string) $value, $terpilih, true))>
                                    <label class="form-check-label" for="{{ $name }}-{{ $loop->index }}">{{ $label }}</label>
                                </div>
                            @empty
                                <div class="text-muted small">Belum ada data master untuk pilihan ini.</div>
                            @endforelse
                        </div>
                        <div class="form-text">Boleh dicentang lebih dari satu.</div>

                    @elseif($field['type'] === 'file')
                        <input type="file" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror">
                        @if($item && $item->{$name})
                            <div class="form-text">File saat ini: <a href="{{ Storage::url($item->{$name}) }}" target="_blank">lihat</a></div>
                        @endif

                    @elseif($field['type'] === 'password')
                        <input type="password" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror" placeholder="{{ $item ? 'Kosongkan jika tidak diubah' : '' }}">

                    @else
                        <input type="{{ $field['type'] }}" name="{{ $name }}" value="{{ $old }}" class="form-control @error($name) is-invalid @enderror" placeholder="{{ $field['placeholder'] ?? '' }}">
                    @endif

                    @error($name)
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach

            <div class="d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                <a href="{{ route($routeName.'.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

@if($pakaiEditor)
    @push('scripts')
        @include('partials.tinymce')
    @endpush
@endif
@endsection
