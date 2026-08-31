@extends('layouts.app')
@section('title', 'Top Products Report')
@section('heading', 'Top Products (by units sold)')

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
        $cards = [['Units Sold', $totals['qty'], false], ['Revenue', $totals['revenue'], true], ['Cost (FIFO)', $totals['cogs'], true], ['Gross Profit', $totals['profit'], true]];
    @endphp
    @foreach ($cards as [$label, $value, $money])
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">{{ $label }}</div>
                <div class="fs-5 fw-bold">{{ $money ? '৳ ' . number_format($value, 2) : number_format($value) }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Product-wise sales &middot; {{ $from }} → {{ $to }}</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>#</th><th>Product</th><th class="text-end">Units</th><th class="text-end">Revenue</th><th class="text-end">COGS</th><th class="text-end">Profit</th><th class="text-end">Margin</th></tr>
            </thead>
            <tbody>
                @forelse ($rows as $i => $r)
                    @php $profit = $r->revenue - $r->cogs; $margin = $r->revenue > 0 ? round($profit / $r->revenue * 100) : 0; @endphp
                    <tr>
                        <td class="text-muted">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $r->name }}</td>
                        <td class="text-end">{{ $r->qty }}</td>
                        <td class="text-end">@money($r->revenue)</td>
                        <td class="text-end text-muted">@money($r->cogs)</td>
                        <td class="text-end fw-semibold {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">@money($profit)</td>
                        <td class="text-end small">{{ $margin }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No sales in this period.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr><td colspan="2">Total</td><td class="text-end">{{ $totals['qty'] }}</td><td class="text-end">@money($totals['revenue'])</td><td class="text-end">@money($totals['cogs'])</td><td class="text-end text-success">@money($totals['profit'])</td><td></td></tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
