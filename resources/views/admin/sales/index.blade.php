@extends('layouts.app')
@section('title', 'Sales')
@section('heading', 'Sales')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-2 flex-grow-1">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="flex:1 1 220px; min-width:180px;" placeholder="Search invoice no...">
                <select name="payment_method" class="form-select form-select-sm" style="flex:0 1 170px; min-width:150px;">
                    <option value="">All Payments</option>
                    @foreach (['cash', 'card', 'mobile', 'due'] as $m)
                        <option value="{{ $m }}" @selected(request('payment_method') === $m)>{{ ucfirst($m) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
            </form>
            <div class="d-flex flex-wrap align-items-center gap-2 ms-auto">
                <a href="{{ route('admin.pos.index') }}" class="btn btn-sm btn-success"><i class="bi bi-cart-check"></i> New Sale (POS)</a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Invoice</th><th>Customer</th><th>Date</th><th>Payment</th><th>Total</th><th>Paid</th><th>Due</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                @forelse ($sales as $s)
                    <tr>
                        <td><a href="{{ route('admin.sales.show', $s) }}" class="fw-semibold">{{ $s->invoice_no }}</a></td>
                        <td class="small">{{ optional($s->customer)->name ?: 'Walk-in' }}</td>
                        <td class="small text-muted">{{ $s->sale_date?->format('d M Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ ucfirst($s->payment_method) }}</span></td>
                        <td>@money($s->total)</td>
                        <td>@money($s->paid)</td>
                        <td>@money($s->due)</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('admin.sales.show', $s) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                            <form action="{{ route('admin.sales.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete sale and return stock?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No sales yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $sales->links() }}</div>
</div>
@endsection
