@extends('layouts.app')
@section('title', 'Create Installment')
@section('heading', 'Create Installment Plan — ' . $sale->invoice_no)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><span class="text-muted">Invoice:</span> <strong>{{ $sale->invoice_no }}</strong></div>
                    <div><span class="text-muted">Total:</span> <strong>@money($sale->total)</strong></div>
                    <div><span class="text-muted">Customer:</span> <strong>{{ optional($sale->customer)->name }}</strong></div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.installments.store', $sale) }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Down Payment</label>
                            <input type="number" step="0.01" min="0" max="{{ $sale->total }}" name="down_payment" value="0" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Number of Installments</label>
                            <input type="number" min="1" max="60" name="num_installments" value="3" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between">
                    <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-outline-secondary">Cancel</a>
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Plan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
