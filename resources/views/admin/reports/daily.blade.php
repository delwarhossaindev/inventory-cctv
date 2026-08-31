@extends('layouts.app')
@section('title', 'Daily Sales Summary')
@section('heading', 'Daily Sales Summary')

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
        $cards = [['Sales', $totals['count'], false], ['Revenue', $totals['revenue'], true], ['Gross Profit', $totals['profit'], true], ['Collected', $totals['collected'], true]];
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
    <div class="card-header bg-white fw-semibold">Day-by-day &middot; {{ $from }} → {{ $to }}</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th class="text-end">Sales</th><th class="text-end">Revenue</th><th class="text-end">COGS</th><th class="text-end">Profit</th><th class="text-end">Collected</th></tr>
            </thead>
            <tbody>
                @forelse ($days as $date => $d)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($date)->format('d M Y, D') }}</td>
                        <td class="text-end">{{ $d['count'] }}</td>
                        <td class="text-end">@money($d['revenue'])</td>
                        <td class="text-end text-muted">@money($d['cogs'])</td>
                        <td class="text-end fw-semibold {{ $d['profit'] >= 0 ? 'text-success' : 'text-danger' }}">@money($d['profit'])</td>
                        <td class="text-end">@money($d['collected'])</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No sales in this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
