@extends('layouts.app')
@section('title', 'Stock')
@section('heading', 'Stock Overview')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small">Total Items in Stock</div>
            <div class="fs-4 fw-bold">{{ number_format($summary['total_items']) }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small">Stock Value (at cost)</div>
            <div class="fs-4 fw-bold">@money($summary['stock_value'])</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <div class="text-muted small">Low Stock Products</div>
            <div class="fs-4 fw-bold text-danger">{{ $summary['low_stock'] }}</div>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <form method="GET" class="row g-2 flex-grow-1">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search product, SKU, model...">
            </div>
            <div class="col-auto">
                <div class="form-check mt-1">
                    <input type="checkbox" name="low" value="1" id="low" class="form-check-input" @checked(request('low')) onchange="this.form.submit()">
                    <label for="low" class="form-check-label small">Low stock only</label>
                </div>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
            </div>
        </form>
        <a href="{{ route('admin.stock.movements') }}" class="btn btn-sm btn-outline-secondary ms-2"><i class="bi bi-clock-history me-1"></i>Movements</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Product</th><th>SKU</th><th class="text-end">Stock</th><th class="text-end">Alert</th><th class="text-end">Cost</th><th class="text-end">Value</th><th class="text-end">Action</th></tr></thead>
            <tbody>
                @forelse ($products as $p)
                    <tr class="{{ $p->isLowStock() ? 'table-danger' : '' }}">
                        <td class="fw-semibold">{{ $p->name }}</td>
                        <td class="small text-muted">{{ $p->sku ?: '—' }}</td>
                        <td class="text-end">{{ $p->stock_quantity }} {{ $p->unit }}</td>
                        <td class="text-end text-muted">{{ $p->alert_quantity }}</td>
                        <td class="text-end">@money($p->purchase_price)</td>
                        <td class="text-end">@money($p->stock_quantity * $p->purchase_price)</td>
                        <td class="text-end"><a href="{{ route('admin.stock.adjust', $p) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-sliders me-1"></i>Adjust</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No products.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $products->links() }}</div>
</div>
@endsection
