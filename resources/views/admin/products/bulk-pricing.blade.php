@extends('layouts.app')
@section('title', 'Bulk Pricing & Stock')
@section('heading', 'Bulk Pricing & Stock')

@section('content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name, SKU, model...">
            </div>
            <div class="col-md-4">
                <select name="main_category_id" class="form-select form-select-sm">
                    <option value="">All Main Categories</option>
                    @foreach ($mains as $m)
                        <option value="{{ $m->id }}" @selected(request('main_category_id') == $m->id)>{{ $m->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('admin.products.bulk-pricing') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('admin.products.bulk-pricing.update') }}">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Edit prices &amp; stock inline, then save</span>
            <button class="btn btn-sm btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th style="width:150px">Purchase Price</th>
                        <th style="width:150px">Sale Price</th>
                        <th style="width:120px">Stock</th>
                        <th style="width:120px">Alert At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $p)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $p->name }}</div>
                                <div class="small text-muted">{{ $p->sku ?: $p->model ?: '—' }} &middot; {{ optional($p->mainCategory)->name }}</div>
                            </td>
                            <td><input type="number" step="0.01" min="0" name="rows[{{ $p->id }}][purchase_price]" value="{{ $p->purchase_price }}" class="form-control form-control-sm"></td>
                            <td><input type="number" step="0.01" min="0" name="rows[{{ $p->id }}][sale_price]" value="{{ $p->sale_price }}" class="form-control form-control-sm"></td>
                            <td><input type="number" min="0" name="rows[{{ $p->id }}][stock]" value="{{ $p->stock_quantity }}" class="form-control form-control-sm"></td>
                            <td><input type="number" min="0" name="rows[{{ $p->id }}][alert_quantity]" value="{{ $p->alert_quantity }}" class="form-control form-control-sm"></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No products found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <div>{{ $products->links() }}</div>
            <button class="btn btn-sm btn-primary"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
        </div>
    </div>
    <p class="text-muted small mt-2"><i class="bi bi-info-circle me-1"></i>Changing stock here records an auditable "adjustment" movement. Saving only affects products shown on this page.</p>
</form>
@endsection
