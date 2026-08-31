@extends('layouts.app')
@section('title', 'Suppliers')
@section('heading', 'Suppliers')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="flex:1 1 240px; min-width:200px;" placeholder="Search name, company, phone...">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
            </form>
            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                <a href="{{ route('admin.suppliers.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add Supplier</a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>#</th><th>Name</th><th>Company</th><th>Phone</th><th>Email</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($suppliers as $s)
                    <tr>
                        <td class="text-muted">{{ $s->id }}</td>
                        <td class="fw-semibold">{{ $s->name }}</td>
                        <td>{{ $s->company ?: '—' }}</td>
                        <td>{{ $s->phone ?: '—' }}</td>
                        <td>{{ $s->email ?: '—' }}</td>
                        <td><span class="badge bg-{{ $s->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($s->status) }}</span></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.suppliers.show', $s) }}" class="btn btn-sm btn-outline-success" title="Ledger"><i class="bi bi-journal-text"></i></a>
                            <a href="{{ route('admin.suppliers.edit', $s) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.suppliers.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete supplier?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No suppliers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $suppliers->links() }}</div>
</div>
@endsection
