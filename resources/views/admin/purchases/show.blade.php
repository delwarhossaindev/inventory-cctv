@extends('layouts.app')
@section('title', $purchase->invoice_no)
@section('heading', 'Purchase ' . $purchase->invoice_no)

@section('content')
@if ($importErrors = session('import_errors'))
    @if (count($importErrors))
        <div class="alert alert-warning">
            <strong>Skipped rows:</strong>
            <ul class="mb-0 small">@foreach ($importErrors as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
@endif

<div class="d-flex justify-content-end gap-2 mb-3">
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
    <button onclick="window.print()" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer me-1"></i>Print</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-6">
                <h5 class="fw-bold mb-1">Purchase Invoice</h5>
                <div class="text-muted">{{ $purchase->invoice_no }}</div>
                <div class="text-muted small">Date: {{ $purchase->purchase_date?->format('d M Y') }}</div>
                <span class="badge bg-{{ $purchase->status === 'received' ? 'success' : 'warning text-dark' }}">{{ ucfirst($purchase->status) }}</span>
            </div>
            <div class="col-6 text-end">
                <div class="text-muted small">Supplier</div>
                <div class="fw-semibold">{{ optional($purchase->supplier)->name ?: '—' }}</div>
                <div class="small text-muted">{{ optional($purchase->supplier)->phone }}</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead class="table-light"><tr><th>#</th><th>Product</th><th class="text-end">Qty</th><th class="text-end">Unit Cost</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($purchase->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ optional($item->product)->name ?: 'Deleted product' }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end">@money($item->unit_cost)</td>
                            <td class="text-end">@money($item->subtotal)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Subtotal</span><span>@money($purchase->subtotal)</span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Discount</span><span>- @money($purchase->discount)</span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Tax</span><span>+ @money($purchase->tax)</span></div>
                <div class="d-flex justify-content-between py-1 border-top fw-bold fs-5"><span>Total</span><span>@money($purchase->total)</span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Paid</span><span>@money($purchase->paid)</span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Due</span><span>@money($purchase->due)</span></div>
            </div>
        </div>

        @if ($purchase->note)
            <div class="mt-3 text-muted small"><strong>Note:</strong> {{ $purchase->note }}</div>
        @endif
    </div>
</div>

{{-- Payment History & Due Payment --}}
@if ($purchase->payments->count() || $purchase->due > 0)
<div class="card border-0 shadow-sm mt-3" id="collect">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-cash-stack me-1"></i>Payments</div>
    <div class="card-body">
        @if ($purchase->payments->count())
            <div class="table-responsive mb-3">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Method</th><th class="text-end">Amount</th><th>Note</th></tr></thead>
                    <tbody>
                        @foreach ($purchase->payments as $p)
                            <tr>
                                <td class="small">{{ $p->payment_date->format('d M Y') }}</td>
                                <td class="small">{{ ucfirst($p->method) }}</td>
                                <td class="text-end fw-semibold">@money($p->amount)</td>
                                <td class="small text-muted">{{ $p->note ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($purchase->due > 0)
            <form action="{{ route('admin.purchases.payments.store', $purchase) }}" method="POST">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-0">Amount (Due: @money($purchase->due))</label>
                        <input type="number" name="amount" step="0.01" min="0.01" max="{{ $purchase->due }}" value="{{ $purchase->due }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Method</label>
                        <select name="method" class="form-select form-select-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="mobile">Mobile</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small mb-0">Date</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-0">Note</label>
                        <input type="text" name="note" class="form-control form-control-sm" placeholder="Optional">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-success w-100"><i class="bi bi-plus-lg me-1"></i>Pay</button>
                    </div>
                </div>
            </form>
        @else
            <div class="text-success small"><i class="bi bi-check-circle me-1"></i>Fully paid</div>
        @endif
    </div>
</div>
@endif
@endsection
