@extends('layouts.app')
@section('title', 'Payments')
@section('heading', 'Payment History')

@section('content')

{{-- Summary --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded bg-success bg-opacity-10 p-3 me-3"><i class="bi bi-arrow-down-circle fs-4 text-success"></i></div>
                <div>
                    <div class="text-muted small">Receivable (Sales due)</div>
                    <div class="fs-5 fw-bold text-success">@money($receivable)</div>
                    <div class="text-muted" style="font-size:.75rem">{{ $dueSales->count() }} unpaid invoice(s)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded bg-danger bg-opacity-10 p-3 me-3"><i class="bi bi-arrow-up-circle fs-4 text-danger"></i></div>
                <div>
                    <div class="text-muted small">Payable (Purchase due)</div>
                    <div class="fs-5 fw-bold text-danger">@money($payable)</div>
                    <div class="text-muted" style="font-size:.75rem">{{ $duePurchases->count() }} unpaid bill(s)</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Outstanding sales (receivable) + search --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="fw-semibold text-success"><i class="bi bi-cash-coin me-1"></i>Outstanding — Collect from Customers</span>
            <div class="ms-auto d-flex flex-wrap align-items-center gap-2">
                @if ($dueCustomers->count())
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#custCollectModal">
                        <i class="bi bi-person-check me-1"></i>Collect from Customer
                    </button>
                @endif
                <form method="GET" id="due-search-form" style="min-width:240px; max-width:360px;">
                    <input type="text" name="q" id="due-search" value="{{ $q }}" autocomplete="off" class="form-control form-control-sm"
                           placeholder="Search customer or invoice no... (auto after 3 letters)">
                </form>
            </div>
        </div>
        @if (!empty($q))
            <div class="form-text mt-1">Showing dues matching "<strong>{{ $q }}</strong>" — totals above reflect all outstanding.</div>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Invoice</th><th>Customer</th><th>Date</th><th class="text-end">Total</th><th class="text-end">Due</th><th class="text-end">Action</th></tr>
            </thead>
            <tbody>
                @forelse ($dueSales as $s)
                    <tr>
                        <td><a href="{{ route('admin.sales.show', $s) }}" class="fw-semibold text-decoration-none">{{ $s->invoice_no }}</a></td>
                        <td class="small">{{ optional($s->customer)->name ?: 'Walk-in' }}</td>
                        <td class="small text-muted">{{ $s->sale_date?->format('d M Y') }}</td>
                        <td class="text-end">@money($s->total)</td>
                        <td class="text-end fw-semibold text-danger">@money($s->due)</td>
                        <td class="text-end"><a href="{{ route('admin.sales.show', $s) }}#collect" class="btn btn-sm btn-success"><i class="bi bi-cash me-1"></i>Collect</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">
                        @if (!empty($q)) No dues match "<strong>{{ $q }}</strong>". @else No outstanding sales due 🎉 @endif
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Outstanding purchases (payable) --}}
@if ($duePurchases->count())
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold text-danger"><i class="bi bi-cash-stack me-1"></i>Outstanding — Pay to Suppliers</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Invoice</th><th>Supplier</th><th>Date</th><th class="text-end">Total</th><th class="text-end">Due</th><th class="text-end">Action</th></tr>
            </thead>
            <tbody>
                @foreach ($duePurchases as $p)
                    <tr>
                        <td><a href="{{ route('admin.purchases.show', $p) }}" class="fw-semibold text-decoration-none">{{ $p->invoice_no }}</a></td>
                        <td class="small">{{ optional($p->supplier)->name ?: '—' }}</td>
                        <td class="small text-muted">{{ $p->purchase_date?->format('d M Y') }}</td>
                        <td class="text-end">@money($p->total)</td>
                        <td class="text-end fw-semibold text-danger">@money($p->due)</td>
                        <td class="text-end"><a href="{{ route('admin.purchases.show', $p) }}#collect" class="btn btn-sm btn-danger"><i class="bi bi-cash me-1"></i>Pay</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-clock-history me-1"></i>Payment History</div>
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <option value="sale" @selected(request('type') === 'sale')>Sale Due Collection</option>
                    <option value="purchase" @selected(request('type') === 'purchase')>Purchase Payment</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Invoice</th>
                    <th class="text-end">Amount</th>
                    <th>Method</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    @php
                        $isSale = $payment->payable_type === \App\Models\Sale::class;
                        $label = $isSale ? 'Sale' : 'Purchase';
                        $invoice = optional($payment->payable)->invoice_no ?? '—';
                        $route = $payment->payable
                            ? ($isSale ? route('admin.sales.show', $payment->payable_id) : route('admin.purchases.show', $payment->payable_id))
                            : '#';
                    @endphp
                    <tr>
                        <td class="small">{{ $payment->payment_date->format('d M Y') }}</td>
                        <td><span class="badge bg-{{ $isSale ? 'success' : 'info' }}">{{ $label }}</span></td>
                        <td><a href="{{ $route }}">{{ $invoice }}</a></td>
                        <td class="text-end fw-semibold">@money($payment->amount)</td>
                        <td class="small">{{ ucfirst($payment->method) }}</td>
                        <td class="small text-muted">{{ $payment->note ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($payments->hasPages())
        <div class="card-footer bg-white">{{ $payments->links() }}</div>
    @endif
</div>

{{-- Customer-wise collection modal --}}
@if ($dueCustomers->count())
<div class="modal fade" id="custCollectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="cust-collect-form" action="#" onsubmit="return jmCustCollect(this);">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-check me-2 text-success"></i>Collect from a Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small mb-1">Customer</label>
                        <select id="cust-select" class="form-select form-select-sm" required>
                            <option value="" data-due="0" data-url="">— Select customer —</option>
                            @foreach ($dueCustomers as $c)
                                <option value="{{ $c->id }}" data-due="{{ $c->due }}"
                                        data-url="{{ route('admin.customers.payments.store', $c->id) }}">
                                    {{ $c->name }}{{ $c->phone ? ' - ' . $c->phone : '' }} — Due ৳{{ number_format($c->due, 2) }} ({{ $c->count }} invoice)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small mb-1">Amount</label>
                            <input type="number" name="amount" id="cust-amount" step="0.01" min="0.01" class="form-control form-control-sm" placeholder="0.00" required disabled>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">Method</label>
                            <select name="method" class="form-select form-select-sm">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small mb-1">Date</label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <input type="hidden" name="note" value="Customer payment">
                    <div class="form-text mt-2" id="cust-hint">Pick a customer — the amount is split automatically across their unpaid invoices (oldest first).</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-sm btn-success" id="cust-collect-btn" disabled><i class="bi bi-cash me-1"></i>Collect</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    var input = document.getElementById('due-search');
    var form = document.getElementById('due-search-form');
    if (!input || !form) return;

    // Keep cursor at the end after an auto-search reload.
    input.focus();
    input.setSelectionRange(input.value.length, input.value.length);

    var timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        var len = input.value.trim().length;
        // Auto-search once 3+ characters are typed, or when the box is cleared.
        if (len >= 3 || len === 0) {
            timer = setTimeout(function () { form.submit(); }, 400);
        }
    });
})();

// Customer-wise collection
(function () {
    var sel = document.getElementById('cust-select');
    var amount = document.getElementById('cust-amount');
    var btn = document.getElementById('cust-collect-btn');
    var hint = document.getElementById('cust-hint');
    if (!sel) return;

    sel.addEventListener('change', function () {
        var opt = sel.options[sel.selectedIndex];
        var due = parseFloat(opt.getAttribute('data-due')) || 0;
        if (sel.value) {
            amount.disabled = false;
            amount.max = due;
            amount.value = due.toFixed(2);
            btn.disabled = false;
            hint.innerHTML = 'Max ৳' + due.toFixed(2) + ' — split oldest invoice first. Enter a smaller amount for partial payment.';
        } else {
            amount.disabled = true; amount.value = ''; btn.disabled = true;
            hint.textContent = 'Pick a customer — the amount is split automatically across their unpaid invoices (oldest first).';
        }
    });
})();

function jmCustCollect(formEl) {
    var sel = document.getElementById('cust-select');
    var amount = document.getElementById('cust-amount');
    var opt = sel.options[sel.selectedIndex];
    var url = opt.getAttribute('data-url');
    var due = parseFloat(opt.getAttribute('data-due')) || 0;
    if (!sel.value || !url) { alert('Please select a customer.'); return false; }
    if (parseFloat(amount.value) > due + 0.001) { alert('Amount cannot exceed the customer due (৳' + due.toFixed(2) + ').'); return false; }
    formEl.action = url;   // post to /customers/{id}/payments
    return true;
}
</script>
@endpush
