@extends('layouts.app')
@section('title', 'Expenses')
@section('heading', 'Expenses')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <div></div>
    <a href="{{ route('admin.expenses.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Expense</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-0">Category</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
            <div class="col-auto ms-auto">
                <div class="text-muted small">Total</div>
                <div class="fw-bold text-danger fs-5">@money($total)</div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th class="text-end">Amount</th>
                    <th>Note</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expenses as $exp)
                    <tr>
                        <td class="small">{{ $exp->expense_date->format('d M Y') }}</td>
                        <td class="fw-semibold">{{ $exp->title }}</td>
                        <td><span class="badge bg-light text-dark border">{{ optional($exp->category)->name ?: 'Uncategorized' }}</span></td>
                        <td class="text-end fw-semibold text-danger">@money($exp->amount)</td>
                        <td class="small text-muted">{{ $exp->note ?: '—' }}</td>
                        <td>
                            <form action="{{ route('admin.expenses.destroy', $exp) }}" method="POST" onsubmit="return confirm('Delete this expense?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger p-0 px-1"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No expenses found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($expenses->hasPages())
        <div class="card-footer bg-white">{{ $expenses->links() }}</div>
    @endif
</div>
@endsection
