@extends('layouts.app')
@section('title', 'Bulk Purchase')
@section('heading', 'Bulk Purchase (Import)')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form action="{{ route('admin.purchases.bulk.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Purchase Info</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Supplier</label>
                            <select name="supplier_id" class="form-select">
                                <option value="">— None —</option>
                                @foreach ($suppliers as $s)
                                    <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="received">Received (add to stock now)</option>
                                <option value="pending">Pending (no stock change)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Discount</label>
                            <input type="number" step="0.01" min="0" name="discount" value="{{ old('discount', 0) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tax</label>
                            <input type="number" step="0.01" min="0" name="tax" value="{{ old('tax', 0) }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Paid</label>
                            <input type="number" step="0.01" min="0" name="paid" value="{{ old('paid', 0) }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Note</label>
                            <textarea name="note" rows="2" class="form-control">{{ old('note') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Items File (CSV / XLSX)</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">File <span class="text-danger">*</span></label>
                        <input type="file" name="file" accept=".csv,.txt,.xlsx" class="form-control" required>
                        <div class="form-text">Columns: <strong>Product</strong> (SKU, barcode, or exact name), <strong>Quantity</strong>, <strong>Unit Cost</strong>. First row = headers.</div>
                    </div>
                    <div class="alert alert-light border small mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Each row becomes a line item. On <strong>Received</strong>, stock is added as a FIFO batch at its unit cost.
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between">
                    <a href="{{ route('admin.purchases.import-template') }}" class="btn btn-outline-secondary"><i class="bi bi-download me-1"></i>Download Template</a>
                    <div>
                        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import Purchase</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
