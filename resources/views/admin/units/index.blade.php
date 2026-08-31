@extends('layouts.app')
@section('title', 'Units')
@section('heading', 'Units')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="flex:1 1 240px; min-width:200px;" placeholder="Search unit...">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
            </form>
            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                <a href="{{ route('admin.units.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Unit</a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Short</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($units as $unit)
                    <tr>
                        <td class="text-muted">{{ $unit->id }}</td>
                        <td class="fw-semibold">{{ $unit->name }}</td>
                        <td>{{ $unit->short_name ?: '—' }}</td>
                        <td><span class="badge bg-{{ $unit->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($unit->status) }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this unit?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No units found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($units->hasPages())
        <div class="card-footer bg-white">{{ $units->links() }}</div>
    @endif
</div>
@endsection
