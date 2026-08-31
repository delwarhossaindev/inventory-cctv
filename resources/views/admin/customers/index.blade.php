@extends('layouts.app')
@section('title', 'Customers')
@section('heading', 'Customers')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="flex:1 1 240px; min-width:200px;" placeholder="Search name, phone...">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
            </form>
            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add Customer</a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($customers as $c)
                    <tr>
                        <td class="text-muted">{{ $c->id }}</td>
                        <td class="fw-semibold">{{ $c->name }}</td>
                        <td>{{ $c->phone ?: '—' }}</td>
                        <td>{{ $c->email ?: '—' }}</td>
                        <td><span class="badge bg-{{ $c->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($c->status) }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.customers.show', $c) }}" class="btn btn-sm btn-outline-success" title="Ledger"><i class="bi bi-journal-text"></i></a>
                            <a href="{{ route('admin.customers.edit', $c) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.customers.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete customer?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $customers->links() }}</div>
</div>
@endsection
