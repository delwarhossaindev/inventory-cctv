@extends('layouts.app')
@section('title', 'Stock Valuation Report')
@section('heading', 'Stock Valuation Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <form method="GET">
        <div class="form-check">
            <input type="checkbox" name="low" value="1" id="low" class="form-check-input" @checked(request('low')) onchange="this.form.submit()">
            <label for="low" class="form-check-label small">Low stock only</label>
        </div>
    </form>
    <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}" target="_blank" class="btn btn-sm btn-danger"><i class="bi bi-file-pdf me-1"></i>PDF</a>
    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer me-1"></i>Print</button>
</div>

<div class="row g-3 mb-3">
    @php
        $cards = [
            ['Products', $totals['products'], 'primary', false],
            ['Total Units', $totals['units'], 'info', false],
            ['Stock Value (cost)', $totals['value'], 'success', true],
            ['Retail Value', $totals['retail'], 'dark', true],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $color, $money])
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="fs-5 fw-bold text-{{ $color }}">{{ $money ? '৳ ' . number_format($value, 2) : number_format($value) }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold">Inventory valued at FIFO cost (remaining batches)</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th><th>SKU</th><th class="text-end">Stock</th>
                    <th class="text-end">Avg Cost</th><th class="text-end">Stock Value</th><th class="text-end">Retail Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $p)
                    @php $avg = $p->stock_quantity > 0 ? $p->stock_value / $p->stock_quantity : 0; @endphp
                    <tr class="{{ $p->isLowStock() ? 'table-warning' : '' }}">
                        <td class="fw-semibold">{{ $p->name }}</td>
                        <td class="small text-muted">{{ $p->sku ?: '—' }}</td>
                        <td class="text-end">{{ $p->stock_quantity }} {{ $p->unit }}</td>
                        <td class="text-end text-muted">@money($avg)</td>
                        <td class="text-end">@money($p->stock_value)</td>
                        <td class="text-end text-muted">@money($p->stock_quantity * $p->sale_price)</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No products.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="4">Total</td>
                    <td class="text-end">@money($totals['value'])</td>
                    <td class="text-end">@money($totals['retail'])</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
