@extends('layouts.app')
@section('title', 'Profit & Loss')
@section('heading', 'Profit & Loss Report')

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

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    @php
        $cards = [
            ['Revenue', $revenue, 'primary', $salesCount . ' sales'],
            ['Cost of Goods', $cogs, 'secondary', null],
            ['Gross Profit', $grossProfit, $grossProfit >= 0 ? 'success' : 'danger', $revenue > 0 ? round($grossProfit / $revenue * 100) . '% margin' : null],
            ['Net Profit', $netProfit, $netProfit >= 0 ? 'success' : 'danger', null],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $color, $sub])
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="fs-5 fw-bold text-{{ $color }}">@money($value)</div>
                    @if ($sub)<div class="text-muted" style="font-size:.75rem">{{ $sub }}</div>@endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    {{-- P&L Statement --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Profit & Loss Statement</div>
            <div class="card-body p-0 table-responsive">
                <table class="table mb-0">
                    <tbody>
                        <tr class="table-light"><td colspan="2" class="fw-semibold text-uppercase small">Income</td></tr>
                        <tr>
                            <td class="ps-4">Sales Revenue</td>
                            <td class="text-end fw-semibold">@money($revenue)</td>
                        </tr>
                        @if ($returns > 0)
                        <tr>
                            <td class="ps-4 text-danger">Less: Sale Returns</td>
                            <td class="text-end text-danger">- @money($returns)</td>
                        </tr>
                        @endif
                        <tr class="border-top">
                            <td class="ps-4 fw-semibold">Net Revenue</td>
                            <td class="text-end fw-semibold">@money($revenue - $returns)</td>
                        </tr>

                        <tr class="table-light"><td colspan="2" class="fw-semibold text-uppercase small">Cost of Goods Sold</td></tr>
                        <tr>
                            <td class="ps-4">COGS (FIFO)</td>
                            <td class="text-end">@money($cogs)</td>
                        </tr>
                        <tr class="border-top {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            <td class="fw-bold">Gross Profit</td>
                            <td class="text-end fw-bold">@money($grossProfit)</td>
                        </tr>

                        <tr class="table-light"><td colspan="2" class="fw-semibold text-uppercase small">Operating Expenses</td></tr>
                        @forelse ($expenseByCategory as $cat => $amt)
                        <tr>
                            <td class="ps-4">{{ $cat }}</td>
                            <td class="text-end">@money($amt)</td>
                        </tr>
                        @empty
                        <tr><td class="ps-4 text-muted" colspan="2">No expenses recorded</td></tr>
                        @endforelse
                        <tr class="border-top">
                            <td class="ps-4 fw-semibold">Total Expenses</td>
                            <td class="text-end fw-semibold">@money($expenses)</td>
                        </tr>

                        <tr class="table-light {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                            <td class="fw-bold fs-5">Net Profit</td>
                            <td class="text-end fw-bold fs-5">@money($netProfit)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Period Summary</div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Period</span>
                    <span class="fw-semibold">{{ $from }} → {{ $to }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Total Sales</span>
                    <span>{{ $salesCount }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Revenue</span>
                    <span>@money($revenue)</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Purchases (Cost)</span>
                    <span>@money($purchases)</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Returns</span>
                    <span class="text-danger">@money($returns)</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Expenses</span>
                    <span class="text-danger">@money($expenses)</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Gross Margin</span>
                    <span class="fw-semibold">{{ $revenue > 0 ? round($grossProfit / $revenue * 100, 1) : 0 }}%</span>
                </div>
                <div class="d-flex justify-content-between py-2 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                    <span class="fw-bold">Net Profit</span>
                    <span class="fw-bold">@money($netProfit)</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
