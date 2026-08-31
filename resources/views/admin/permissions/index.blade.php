@extends('layouts.app')
@section('title', 'Permissions')
@section('heading', 'Permissions')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search permission...">
            </div>
            <div class="col-md-3">
                <select name="group" class="form-select form-select-sm">
                    <option value="">All Groups</option>
                    @foreach ($groups as $g)
                        <option value="{{ $g }}" @selected(request('group') === $g)>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col text-end">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                <a href="{{ route('admin.permissions.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add Permission</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>#</th><th>Permission</th><th>Group</th><th>Used by roles</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($permissions as $perm)
                    <tr>
                        <td class="text-muted">{{ $perm->id }}</td>
                        <td class="fw-semibold text-capitalize">{{ $perm->name }}</td>
                        <td>@if($perm->group)<span class="badge bg-light text-dark border">{{ $perm->group }}</span>@else<span class="text-muted">—</span>@endif</td>
                        <td>{{ $perm->roles_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.permissions.edit', $perm) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.permissions.destroy', $perm) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this permission? It will be removed from all roles.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No permissions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $permissions->links() }}</div>
</div>
@endsection
