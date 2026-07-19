@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <form class="d-flex gap-2" method="GET">
            <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Cari...">
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
        </form>
        <div class="d-flex gap-2">
            @if($canManage)
            <a href="{{ route($routeName.'.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Tambah
            </a>
            @endif
            @foreach($extraActions ?? [] as $action)
            <a href="{{ $action['url'] }}" class="btn btn-sm btn-outline-secondary">
                @if(!empty($action['icon']))<i class="bi {{ $action['icon'] }} me-1"></i>@endif{{ $action['label'] }}
            </a>
            @endforeach
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:40px;">#</th>
                    @foreach($fields as $field)
                        @if($field['list'] ?? true)
                            <th>{{ $field['label'] }}</th>
                        @endif
                    @endforeach
                    <th style="width:120px;" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $loop->iteration + ($items->currentPage()-1) * $items->perPage() }}</td>
                        @foreach($fields as $field)
                            @if($field['list'] ?? true)
                                <td>
                                    @php
                                        $val = isset($field['relation'])
                                            ? data_get($item, $field['relation']['method'].'.'.$field['relation']['display'])
                                            : (isset($field['options']) ? ($field['options'][$item->{$field['name']}] ?? $item->{$field['name']}) : $item->{$field['name']});
                                    @endphp
                                    @if($field['type'] === 'file' && $item->{$field['name']})
                                        <a href="{{ Storage::url($item->{$field['name']}) }}" target="_blank">Lihat File</a>
                                    @elseif($field['type'] === 'checkbox')
                                        {{ $item->{$field['name']} ? 'Ya' : 'Tidak' }}
                                    @else
                                        {{ Illuminate\Support\Str::limit($val, 60) }}
                                    @endif
                                </td>
                            @endif
                        @endforeach
                        <td class="text-end">
                            @if($canManage)
                            <a href="{{ route($routeName.'.edit', $item->id) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route($routeName.'.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @else
                                <span class="text-muted small">Lihat saja</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="20" class="text-center text-muted py-4">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
    <div class="card-footer bg-white">
        {{ $items->links() }}
    </div>
    @endif
</div>
@endsection
