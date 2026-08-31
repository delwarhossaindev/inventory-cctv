@extends('layouts.app')
@section('title', 'Trial Balance')
@section('heading', 'Trial Balance')

@section('content')
{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small mb-0">As at Date</label>
                <input type="date" name="date" value="{{ $asAt }}" class="form-control form-control-sm">
            </div>
            <div class="col-auto d-flex gap-1">
                <button class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
                <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank" class="btn btn-sm btn-danger"><i class="bi bi-file-pdf me-1"></i>PDF</a>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-3">
    @php
        $cards = [
            ['Sales Revenue', $revenue, 'bi-cash-coin', 'success'],
            ['Cost of Goods Sold', $cogs, 'bi-box-arrow-up', 'warning'],
            ['Gross Profit', $grossProfit, 'bi-graph-up', $grossProfit >= 0 ? 'info' : 'danger'],
            ['Net Profit', $netProfit, 'bi-currency-dollar', $netProfit >= 0 ? 'primary' : 'danger'],
        ];
    @endphp
    @foreach ($cards as [$lbl, $val, $ico, $col])
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded bg-{{ $col }} bg-opacity-10 p-3 me-3"><i class="bi {{ $ico }} fs-4 text-{{ $col }}"></i></div>
                    <div>
                        <div class="text-muted small">{{ $lbl }}</div>
                        <div class="fs-5 fw-bold text-{{ $col }}">@money($val)</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Two-column Trial Balance table --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-list-columns-reverse me-1"></i>Trial Balance</span>
        <span class="text-muted small">As at {{ \Carbon\Carbon::parse($asAt)->format('d M Y') }}</span>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-bordered table-sm mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="min-width:170px">Particulars (Dr)</th>
                    <th class="text-end" style="width:170px">Amount (৳)</th>
                    <th style="min-width:170px">Particulars (Cr)</th>
                    <th class="text-end" style="width:170px">Amount (৳)</th>
                </tr>
            </thead>
            <tbody>
                @php $maxRows = max(count($debit), count($credit)); @endphp
                @for ($i = 0; $i < $maxRows; $i++)
                    @php
                        $dr = $debit[$i] ?? null;
                        $cr = $credit[$i] ?? null;
                        $typeBadge = [
                            'Asset'    => 'bg-info',
                            'Expense'  => 'bg-warning text-dark',
                            'Contra'   => 'bg-secondary',
                            'Loss'     => 'bg-danger',
                            'Revenue'  => 'bg-success',
                            'Liability'=> 'bg-warning text-dark',
                            'Equity'   => 'bg-primary',
                        ];
                    @endphp
                    <tr>
                        <td>
                            @if ($dr)
                                <span class="badge {{ $typeBadge[$dr[2]] ?? 'bg-secondary' }} me-1" style="font-size:.65rem">{{ $dr[2] }}</span>
                                {{ $dr[0] }}
                            @endif
                        </td>
                        <td class="text-end{{ $dr ? ' fw-semibold' : '' }}">
                            @if ($dr) ৳ {{ number_format($dr[1], 2) }} @endif
                        </td>
                        <td>
                            @if ($cr)
                                <span class="badge {{ $typeBadge[$cr[2]] ?? 'bg-secondary' }} me-1" style="font-size:.65rem">{{ $cr[2] }}</span>
                                {{ $cr[0] }}
                            @endif
                        </td>
                        <td class="text-end{{ $cr ? ' fw-semibold' : '' }}">
                            @if ($cr) ৳ {{ number_format($cr[1], 2) }} @endif
                        </td>
                    </tr>
                @endfor
            </tbody>
            <tfoot class="table-dark fw-bold">
                <tr>
                    <td>Total</td>
                    <td class="text-end">৳ {{ number_format($totalDr, 2) }}</td>
                    <td>Total</td>
                    <td class="text-end">৳ {{ number_format($totalCr, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- P&L Summary + Balance Sheet side-by-side --}}
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-bar-chart-line me-1"></i>Profit & Loss Summary</div>
            <table class="table table-sm mb-0">
                <tbody>
                    <tr>
                        <td>Sales Revenue</td>
                        <td class="text-end text-success fw-semibold">@money($revenue)</td>
                    </tr>
                    @if ($returns > 0)
                    <tr>
                        <td class="ps-4 text-muted">Less: Sale Returns</td>
                        <td class="text-end text-danger">(৳ {{ number_format($returns, 2) }})</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="ps-4 text-muted">Less: Cost of Goods Sold</td>
                        <td class="text-end text-danger">(৳ {{ number_format($cogs, 2) }})</td>
                    </tr>
                    <tr class="fw-semibold table-light">
                        <td>Gross Profit</td>
                        <td class="text-end {{ $grossProfit >= 0 ? 'text-info' : 'text-danger' }}">@money($grossProfit)</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-muted">Less: Operating Expenses</td>
                        <td class="text-end text-danger">(৳ {{ number_format($expenses, 2) }})</td>
                    </tr>
                    <tr class="fw-bold table-dark">
                        <td>Net Profit{{ $netProfit < 0 ? ' (Loss)' : '' }}</td>
                        <td class="text-end {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">@money($netProfit)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-layout-three-columns me-1"></i>Balance Sheet Position</div>
            <table class="table table-sm mb-0">
                <tbody>
                    <tr class="table-light">
                        <td colspan="2" class="fw-semibold text-info small text-uppercase">Assets</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-muted">Cash & Bank{{ $cash < 0 ? ' (Overdraft)' : '' }}</td>
                        <td class="text-end {{ $cash < 0 ? 'text-danger' : '' }}">@money($cash)</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-muted">Accounts Receivable</td>
                        <td class="text-end">@money($receivables)</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-muted">Closing Inventory (FIFO)</td>
                        <td class="text-end">@money($inventory)</td>
                    </tr>
                    @php $totalAssets = $cash + $receivables + $inventory; @endphp
                    <tr class="fw-semibold table-light">
                        <td>Total Assets</td>
                        <td class="text-end">@money($totalAssets)</td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="2" class="fw-semibold text-warning small text-uppercase">Liabilities</td>
                    </tr>
                    <tr>
                        <td class="ps-4 text-muted">Accounts Payable</td>
                        <td class="text-end">@money($payables)</td>
                    </tr>
                    <tr class="fw-semibold table-light">
                        <td>Total Liabilities</td>
                        <td class="text-end">@money($payables)</td>
                    </tr>
                    @php $netWorth = $totalAssets - $payables; @endphp
                    <tr class="fw-bold table-dark">
                        <td>Net Worth (Assets − Liabilities)</td>
                        <td class="text-end {{ $netWorth >= 0 ? 'text-success' : 'text-danger' }}">@money($netWorth)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
