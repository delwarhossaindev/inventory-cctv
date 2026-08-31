@extends('layouts.app')
@section('title', 'Sales & Profit Report')
@section('heading', 'Sales & Profit Report')

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
        $cards = [
            ['Revenue', $totals['revenue'], 'primary'],
            ['Cost (FIFO)', $totals['cogs'], 'secondary'],
            ['Gross Profit', $totals['profit'], $totals['profit'] >= 0 ? 'success' : 'danger'],
            ['Collected', $totals['collected'], 'info'],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $color])
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="fs-5 fw-bold text-{{ $color }}">@money($value)</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">{{ $totals['count'] }} sales &middot; {{ $from }} → {{ $to }}</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice</th><th>Date</th><th>Customer</th>
                    <th class="text-end">Revenue</th><th class="text-end">COGS</th><th class="text-end">Profit</th><th class="text-end">Margin</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $s)
                    @php $profit = $s->revenue - $s->cogs; $margin = $s->revenue > 0 ? round($profit / $s->revenue * 100) : 0; @endphp
                    <tr>
                        <td><a href="{{ route('admin.sales.show', $s) }}">{{ $s->invoice_no }}</a></td>
                        <td class="small text-muted">{{ $s->sale_date?->format('d M Y') }}</td>
                        <td class="small">{{ optional($s->customer)->name ?: 'Walk-in' }}</td>
                        <td class="text-end">@money($s->revenue)</td>
                        <td class="text-end text-muted">@money($s->cogs)</td>
                        <td class="text-end fw-semibold {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">@money($profit)</td>
                        <td class="text-end small">{{ $margin }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No sales in this period.</td></tr>
                @endforelse
            </tbody>
            @if ($sales->isNotEmpty())
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3">Total</td>
                        <td class="text-end">@money($totals['revenue'])</td>
                        <td class="text-end">@money($totals['cogs'])</td>
                        <td class="text-end text-success">@money($totals['profit'])</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
