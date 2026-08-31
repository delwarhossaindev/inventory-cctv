@extends('layouts.app')
@section('title', 'Purchase Report')
@section('heading', 'Purchase Report')

@section('content')
<div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-0">From</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">To</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">All suppliers</option>
                    @foreach ($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
                <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank" class="btn btn-sm btn-danger"><i class="bi bi-file-pdf me-1"></i>PDF</a>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    @php
        $cards = [['Purchases', $totals['count'], false], ['Total', $totals['total'], true], ['Paid', $totals['paid'], true], ['Due', $totals['due'], true]];
    @endphp
    @foreach ($cards as [$label, $value, $money])
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">{{ $label }}</div>
                <div class="fs-5 fw-bold">{{ $money ? '৳ ' . number_format($value, 2) : $value }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">{{ $totals['count'] }} purchases &middot; {{ $from }} → {{ $to }}</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Invoice</th><th>Date</th><th>Supplier</th><th>Status</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Due</th></tr>
            </thead>
            <tbody>
                @forelse ($purchases as $p)
                    <tr>
                        <td><a href="{{ route('admin.purchases.show', $p) }}">{{ $p->invoice_no }}</a></td>
                        <td class="small text-muted">{{ $p->purchase_date?->format('d M Y') }}</td>
                        <td class="small">{{ optional($p->supplier)->name ?: '—' }}</td>
                        <td><span class="badge bg-{{ $p->status === 'received' ? 'success' : 'warning text-dark' }}">{{ ucfirst($p->status) }}</span></td>
                        <td class="text-end">@money($p->total)</td>
                        <td class="text-end text-muted">@money($p->paid)</td>
                        <td class="text-end {{ $p->due > 0 ? 'text-danger' : '' }}">@money($p->due)</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No purchases in this period.</td></tr>
                @endforelse
            </tbody>
            @if ($purchases->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr><td colspan="4">Total</td><td class="text-end">@money($totals['total'])</td><td class="text-end">@money($totals['paid'])</td><td class="text-end">@money($totals['due'])</td></tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
