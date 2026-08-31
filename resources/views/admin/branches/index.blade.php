@extends('layouts.app')
@section('title', 'Branches')
@section('heading', 'Branches / Stores')

@section('content')
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Add Branch</div>
            <div class="card-body">
                <form action="{{ route('admin.branches.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="Branch name" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="address" class="form-control form-control-sm" placeholder="Address">
                    </div>
                    <div class="mb-2">
                        <input type="text" name="phone" class="form-control form-control-sm" placeholder="Phone">
                    </div>
                    <button class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Branch</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">All Branches</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Address</th><th>Phone</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($branches as $b)
                            <tr>
                                <td class="fw-semibold">{{ $b->name }}</td>
                                <td class="small text-muted">{{ $b->address ?: '—' }}</td>
                                <td class="small">{{ $b->phone ?: '—' }}</td>
                                <td>
                                    <form action="{{ route('admin.branches.destroy', $b) }}" method="POST" onsubmit="return confirm('Delete?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger p-0 px-1"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No branches yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
