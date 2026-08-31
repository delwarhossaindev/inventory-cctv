@extends('layouts.app')
@section('title', $supplier->name . ' — Ledger')
@section('heading', 'Supplier Ledger')

@section('content')
<div class="d-flex justify-content-end gap-2 mb-3">
    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width:56px;height:56px">
                        <i class="bi bi-truck fs-3 text-info"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">{{ $supplier->name }}</h5>
                        @if ($supplier->company)<div class="text-muted small">{{ $supplier->company }}</div>@endif
                        @if ($supplier->phone)<div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $supplier->phone }}</div>@endif
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row g-2 text-center">
                    <div class="col-3">
                        <div class="text-muted small">Total Purchases</div>
                        <div class="fw-bold">{{ $totals['purchases'] }}</div>
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

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-bag-check me-1"></i>Purchase History</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Invoice</th><th>Date</th><th>Status</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Due</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($purchases as $p)
                    <tr>
                        <td><a href="{{ route('admin.purchases.show', $p) }}">{{ $p->invoice_no }}</a></td>
                        <td class="small">{{ $p->purchase_date?->format('d M Y') }}</td>
                        <td><span class="badge bg-{{ $p->status === 'received' ? 'success' : 'warning text-dark' }}">{{ ucfirst($p->status) }}</span></td>
                        <td class="text-end">@money($p->total)</td>
                        <td class="text-end text-success">@money($p->paid + $p->payments->sum('amount'))</td>
                        <td class="text-end {{ $p->due > 0 ? 'text-danger fw-semibold' : '' }}">@money($p->due)</td>
                        <td><a href="{{ route('admin.purchases.show', $p) }}" class="btn btn-sm btn-outline-secondary p-0 px-1"><i class="bi bi-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No purchases from this supplier.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
