@extends('layouts.app')
@section('title', 'Day Book')
@section('heading', 'Day Book')

@section('content')
{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small mb-0">From</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto">
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

{{-- Summary --}}
<div class="row g-3 mb-3">
    @php
        $cards = [
            ['Opening Balance', $totals['opening'], 'bi-wallet2', 'secondary'],
            ['Total Income', $totals['income'], 'bi-arrow-down-circle', 'success'],
            ['Total Expense', $totals['expense'], 'bi-arrow-up-circle', 'danger'],
            ['Closing Balance', $totals['closing'], 'bi-cash-stack', $totals['closing'] >= 0 ? 'primary' : 'danger'],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $icon, $color])
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded bg-{{ $color }} bg-opacity-10 p-3 me-3"><i class="bi {{ $icon }} fs-4 text-{{ $color }}"></i></div>
                    <div>
                        <div class="text-muted small">{{ $label }}</div>
                        <div class="fs-5 fw-bold text-{{ $color }}">৳ {{ number_format($value, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Source-wise breakdown --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-success"><i class="bi bi-arrow-down-circle me-1"></i>Income Breakdown</div>
            <ul class="list-group list-group-flush">
                @foreach ($breakdown['income'] as $label => $amount)
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">{{ $label }}</span>
                        <span class="fw-semibold">৳ {{ number_format($amount, 2) }}</span>
                    </li>
                @endforeach
                <li class="list-group-item d-flex justify-content-between bg-light">
                    <span class="fw-bold">Total Income</span>
                    <span class="fw-bold text-success">৳ {{ number_format($totals['income'], 2) }}</span>
                </li>
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold text-danger"><i class="bi bi-arrow-up-circle me-1"></i>Expense Breakdown</div>
            <ul class="list-group list-group-flush">
                @foreach ($breakdown['expense'] as $label => $amount)
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">{{ $label }}</span>
                        <span class="fw-semibold">৳ {{ number_format($amount, 2) }}</span>
                    </li>
                @endforeach
                <li class="list-group-item d-flex justify-content-between bg-light">
                    <span class="fw-bold">Total Expense</span>
                    <span class="fw-bold text-danger">৳ {{ number_format($totals['expense'], 2) }}</span>
                </li>
            </ul>
        </div>
    </div>
</div>

{{-- Day-by-day --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-journal-bookmark me-1"></i>Daily Cash Flow ({{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }} — {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }})</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th class="text-end">Income (In)</th>
                    <th class="text-end">Expense (Out)</th>
                    <th class="text-end">Net</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-light">
                    <td class="fw-semibold">Opening Balance</td>
                    <td></td><td></td><td></td>
                    <td class="text-end fw-semibold">৳ {{ number_format($totals['opening'], 2) }}</td>
                </tr>
                @forelse ($rows as $r)
                    <tr>
                        <td class="small">{{ \Illuminate\Support\Carbon::parse($r['date'])->format('d M Y') }}</td>
                        <td class="text-end text-success">{{ $r['income'] ? '৳ ' . number_format($r['income'], 2) : '—' }}</td>
                        <td class="text-end text-danger">{{ $r['expense'] ? '৳ ' . number_format($r['expense'], 2) : '—' }}</td>
                        <td class="text-end {{ $r['net'] >= 0 ? 'text-success' : 'text-danger' }}">৳ {{ number_format($r['net'], 2) }}</td>
                        <td class="text-end fw-semibold">৳ {{ number_format($r['balance'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No transactions in this period.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td>Total</td>
                    <td class="text-end text-success">৳ {{ number_format($totals['income'], 2) }}</td>
                    <td class="text-end text-danger">৳ {{ number_format($totals['expense'], 2) }}</td>
                    <td class="text-end {{ $totals['net'] >= 0 ? 'text-success' : 'text-danger' }}">৳ {{ number_format($totals['net'], 2) }}</td>
                    <td class="text-end">৳ {{ number_format($totals['closing'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="card-footer bg-white small text-muted">
        Cash-basis. Income = cash sales + due collections. Expense = expenses + purchase payments + sale returns.
    </div>
</div>
@endsection
