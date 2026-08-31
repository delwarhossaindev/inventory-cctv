@extends('layouts.app')
@section('title', $customer->name . ' — Ledger')
@section('heading', 'Customer Ledger')

@section('content')
<div class="d-flex justify-content-end gap-2 mb-3">
    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
    <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:56px;height:56px">
                        <i class="bi bi-person fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $customer->name }}</h5>
                        @if ($customer->phone)<div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $customer->phone }}</div>@endif
                        @if ($customer->email)<div class="text-muted small"><i class="bi bi-envelope me-1"></i>{{ $customer->email }}</div>@endif
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row g-2 text-center">
                    <div class="col-3">
                        <div class="text-muted small">Total Sales</div>
                        <div class="fw-bold">{{ $totals['sales'] }}</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted small">Total Amount</div>
                        <div class="fw-bold text-primary">@money($totals['total'])</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted small">Total Paid</div>
                        <div class="fw-bold text-success">@money($totals['paid'])</div>
                    </div>
                    <div class="col-3">
                        <div class="text-muted small">Due Balance</div>
                        <div class="fw-bold {{ $totals['due'] > 0 ? 'text-danger' : 'text-success' }}">@money($totals['due'])</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($totals['due'] > 0)
<div class="card border-0 shadow-sm mb-3" id="collect">
    <div class="card-header bg-white fw-semibold text-success"><i class="bi bi-cash-coin me-1"></i>Collect Payment (applied to oldest invoice first)</div>
    <div class="card-body">
        <form action="{{ route('admin.customers.payments.store', $customer) }}" method="POST">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-0">Amount (Due: @money($totals['due']))</label>
                    <input type="number" name="amount" step="0.01" min="0.01" max="{{ $totals['due'] }}" value="{{ $totals['due'] }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Method</label>
                    <select name="method" class="form-select form-select-sm">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="mobile">Mobile</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-0">Date</label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-0">Note</label>
                    <input type="text" name="note" class="form-control form-control-sm" placeholder="Optional">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-success w-100"><i class="bi bi-cash me-1"></i>Collect</button>
                </div>
            </div>
            <div class="form-text">This amount is split automatically across the customer's unpaid invoices, starting from the oldest.</div>
        </form>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-receipt me-1"></i>Sales History</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Invoice</th><th>Date</th><th>Payment</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Due</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr>
                        <td><a href="{{ route('admin.sales.show', $sale) }}">{{ $sale->invoice_no }}</a></td>
                        <td class="small">{{ $sale->sale_date?->format('d M Y') }}</td>
                        <td><span class="badge bg-light text-dark border">{{ ucfirst($sale->payment_method) }}</span></td>
                        <td class="text-end">@money($sale->total)</td>
                        <td class="text-end text-success">@money($sale->paid + $sale->payments->sum('amount'))</td>
                        <td class="text-end {{ $sale->due > 0 ? 'text-danger fw-semibold' : '' }}">@money($sale->due)</td>
                        <td><a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-outline-secondary p-0 px-1"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No sales yet for this customer.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
