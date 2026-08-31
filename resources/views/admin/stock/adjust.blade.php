@extends('layouts.app')
@section('title', 'Adjust Stock')
@section('heading', 'Adjust Stock')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <div class="fw-semibold">{{ $product->name }}</div>
                <div class="small text-muted">Current stock: <strong>{{ $product->stock_quantity }} {{ $product->unit }}</strong></div>
            </div>
            <form action="{{ route('admin.stock.adjust.store', $product) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select name="mode" class="form-select">
                            <option value="add">Add to stock (+)</option>
                            <option value="subtract">Remove from stock (−)</option>
                            <option value="set">Set exact stock value</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="value" min="0" value="0" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason / Note</label>
                        <textarea name="note" rows="2" class="form-control" placeholder="e.g. damaged, stock count correction..."></textarea>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Apply Adjustment</button>
                    <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
