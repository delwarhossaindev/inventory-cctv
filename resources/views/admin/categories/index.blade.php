@extends('layouts.app')
@section('title', 'Categories')
@section('heading', 'Categories')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="flex:1 1 220px; min-width:180px;" placeholder="Search category...">
                <select name="level" class="form-select form-select-sm" style="flex:0 1 170px; min-width:150px;">
                    <option value="">All Levels</option>
                    @foreach (\App\Models\Category::LEVELS as $val => $label)
                        <option value="{{ $val }}" @selected(request('level') == $val)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
                <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add</a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Level</th><th>Parent</th><th>Status</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($categories as $cat)
                    <tr>
                        <td class="text-muted">{{ $cat->id }}</td>
                        <td>
                            <span class="fw-semibold">{{ $cat->name }}</span>
                            <div class="small text-muted">{{ $cat->slug }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $cat->level_name }}</span></td>
                        <td class="small text-muted">{{ optional($cat->parent)->name ?: '—' }}</td>
                        <td><span class="badge bg-{{ $cat->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($cat->status) }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $categories->links() }}</div>
</div>
@endsection
