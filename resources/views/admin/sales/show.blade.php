@extends('layouts.app')
@section('title', $sale->invoice_no)
@section('heading', 'Sale ' . $sale->invoice_no)

@section('content')
<div class="d-flex justify-content-end gap-2 mb-3">
    <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary">Back</a>
    <a href="{{ route('admin.pos.index') }}" class="btn btn-sm btn-success"><i class="bi bi-cart-check me-1"></i>New Sale</a>
    <a href="{{ route('admin.sales.invoice', $sale) }}" class="btn btn-sm btn-primary" target="_blank"><i class="bi bi-file-pdf me-1"></i>Invoice PDF</a>
    <a href="{{ route('admin.sales.invoice', ['sale' => $sale, 'download' => 1]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download me-1"></i>Download</a>
    <a href="{{ route('admin.sales.receipt', $sale) }}" class="btn btn-sm btn-outline-info" target="_blank"><i class="bi bi-receipt me-1"></i>Thermal Receipt</a>
    @if ($sale->customer_id)
        <a href="{{ route('admin.installments.create', $sale) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-calendar2-check me-1"></i>EMI</a>
    @endif
    <a href="{{ route('admin.returns.create', $sale) }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-arrow-return-left me-1"></i>Return</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-6">
                <h5 class="fw-bold mb-1">Sales Invoice</h5>
                <div class="text-muted">{{ $sale->invoice_no }}</div>
                <div class="text-muted small">Date: {{ $sale->sale_date?->format('d M Y') }}</div>
            </div>
            <div class="col-6 text-end">
                <div class="text-muted small">Customer</div>
                <div class="fw-semibold">{{ optional($sale->customer)->name ?: 'Walk-in Customer' }}</div>
                <div class="small text-muted">{{ optional($sale->customer)->phone }}</div>
                <span class="badge bg-light text-dark border mt-1">{{ ucfirst($sale->payment_method) }}</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead class="table-light"><tr><th>#</th><th>Product</th><th class="text-end">Qty</th><th class="text-end">Unit Price</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($sale->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ optional($item->product)->name ?: 'Deleted product' }}</td>
                            <td class="text-end">{{ $item->quantity }}</td>
                            <td class="text-end">@money($item->unit_price)</td>
                            <td class="text-end">@money($item->subtotal)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Subtotal</span><span>@money($sale->subtotal)</span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Discount</span><span>- @money($sale->discount)</span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Tax</span><span>+ @money($sale->tax)</span></div>
                <div class="d-flex justify-content-between py-1 border-top fw-bold fs-5"><span>Total</span><span>@money($sale->total)</span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Paid</span><span>@money($sale->paid)</span></div>
                <div class="d-flex justify-content-between py-1"><span class="text-muted">Due</span><span>@money($sale->due)</span></div>
            </div>
        </div>

        @php
            $cogs = $sale->items->sum('cost_total');
            $profit = $sale->subtotal - $cogs;
        @endphp
        <div class="row justify-content-end mt-2">
            <div class="col-md-5">
                <div class="border rounded bg-light p-2">
                    <div class="d-flex justify-content-between py-1 small"><span class="text-muted">Cost of Goods (FIFO)</span><span>@money($cogs)</span></div>
                    <div class="d-flex justify-content-between py-1 fw-semibold {{ $profit >= 0 ? 'text-success' : 'text-danger' }}"><span>Gross Profit</span><span>@money($profit)</span></div>
                </div>
            </div>
        </div>

        @if ($sale->note)
            <div class="mt-3 text-muted small"><strong>Note:</strong> {{ $sale->note }}</div>
        @endif
    </div>
</div>

{{-- Payment History & Due Collection --}}
@if ($sale->payments->count() || $sale->due > 0)
<div class="card border-0 shadow-sm mt-3" id="collect">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-cash-stack me-1"></i>Payments</div>
    <div class="card-body">
        @if ($sale->payments->count())
            <div class="table-responsive mb-3">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Method</th><th class="text-end">Amount</th><th>Note</th></tr></thead>
                    <tbody>
                        @foreach ($sale->payments as $p)
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

        @if ($sale->due > 0)
            <form action="{{ route('admin.sales.payments.store', $sale) }}" method="POST">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small mb-0">Amount (Due: @money($sale->due))</label>
                        <input type="number" name="amount" step="0.01" min="0.01" max="{{ $sale->due }}" value="{{ $sale->due }}" class="form-control form-control-sm" required>
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
                        <button class="btn btn-sm btn-success w-100"><i class="bi bi-plus-lg me-1"></i>Collect</button>
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
