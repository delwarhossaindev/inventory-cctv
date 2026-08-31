@extends('layouts.app')
@section('title', 'Roles')
@section('heading', 'Roles & Permissions')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Roles</span>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add Role</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>#</th><th>Role</th><th>Permissions</th><th>Users</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr>
                        <td class="text-muted">{{ $role->id }}</td>
                        <td class="fw-semibold">
                            {{ $role->name }}
                            @if ($role->name === 'Super Admin')<span class="badge bg-dark ms-1">full access</span>@endif
                        </td>
                        <td>{{ $role->name === 'Super Admin' ? 'All' : $role->permissions_count }}</td>
                        <td>{{ $role->users_count }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" @disabled($role->name === 'Super Admin')><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
