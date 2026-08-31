@extends('layouts.app')
@section('title', 'Users')
@section('heading', 'Users')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="flex:1 1 240px; min-width:200px;" placeholder="Search name or email...">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
            </form>
            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add User</a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>#</th><th>Name</th><th>Email</th><th>Roles</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="text-muted">{{ $user->id }}</td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @forelse ($user->roles as $role)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $role->name }}</span>
                            @empty
                                <span class="text-muted small">No role</span>
                            @endforelse
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" @disabled($user->id === auth()->id())><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $users->links() }}</div>
</div>
@endsection
