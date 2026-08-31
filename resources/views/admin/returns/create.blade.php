@extends('layouts.app')
@section('title', 'Return — ' . $sale->invoice_no)
@section('heading', 'Sale Return — ' . $sale->invoice_no)

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Invoice</div>
                <div class="fw-bold">{{ $sale->invoice_no }}</div>
                <div class="text-muted small mt-1">Date: {{ $sale->sale_date?->format('d M Y') }}</div>
                <div class="text-muted small">Customer: {{ optional($sale->customer)->name ?: 'Walk-in' }}</div>
                <div class="text-muted small">Total: @money($sale->total)</div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.returns.store', $sale) }}" method="POST" id="return-form">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Select items to return</div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-3">
                    <label class="form-label small">Return Date</label>
                    <input type="date" name="return_date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Reason</label>
                    <select name="reason" class="form-select form-select-sm">
                        <option value="">Select reason</option>
                        <option value="Defective">Defective / Damaged</option>
                        <option value="Wrong item">Wrong item delivered</option>
                        <option value="Customer changed mind">Customer changed mind</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small">Note</label>
                    <input type="text" name="note" class="form-control form-control-sm" placeholder="Optional note">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="check-all"></th>
                            <th>Product</th>
                            <th class="text-end">Sold Qty</th>
                            <th class="text-end">Returned</th>
                            <th class="text-end">Returnable</th>
                            <th class="text-end">Unit Price</th>
                            <th style="width:120px">Return Qty</th>
                            <th class="text-end">Refund</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $i => $item)
                            @php
                                $alreadyReturned = (int) ($returnedByProduct[$item->product_id] ?? 0);
                                $returnable = max($item->quantity - $alreadyReturned, 0);
                            @endphp
                            <tr class="return-row {{ $returnable <= 0 ? 'table-secondary' : '' }}">
                                <td><input type="checkbox" class="item-check" data-index="{{ $i }}" {{ $returnable <= 0 ? 'disabled' : '' }}></td>
                                <td>
                                    {{ optional($item->product)->name ?: 'Deleted product' }}
                                    @if ($returnable <= 0)<span class="badge bg-secondary ms-1">Fully returned</span>@endif
                                </td>
                                <td class="text-end">{{ $item->quantity }}</td>
                                <td class="text-end text-muted">{{ $alreadyReturned ?: '—' }}</td>
                                <td class="text-end fw-semibold">{{ $returnable }}</td>
                                <td class="text-end">@money($item->unit_price)</td>
                                <td>
                                    <input type="number" min="0" max="{{ $returnable }}" value="0"
                                           class="form-control form-control-sm return-qty" data-index="{{ $i }}"
                                           data-price="{{ $item->unit_price }}" data-max="{{ $returnable }}" disabled>
                                    <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}" disabled>
                                    <input type="hidden" name="items[{{ $i }}][quantity]" value="0" class="hidden-qty" disabled>
                                    <input type="hidden" name="items[{{ $i }}][unit_price]" value="{{ $item->unit_price }}" disabled>
                                </td>
                                <td class="text-end refund-amount">৳ 0.00</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="7" class="text-end fw-bold">Total Refund</td>
                            <td class="text-end fw-bold text-danger" id="total-refund">৳ 0.00</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between">
            <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-danger" id="submit-btn" disabled>
                <i class="bi bi-arrow-return-left me-1"></i>Process Return
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('.return-row');
    const checkAll = document.getElementById('check-all');
    const submitBtn = document.getElementById('submit-btn');

    function update() {
        let total = 0;
        let anyChecked = false;
        rows.forEach(row => {
            const check = row.querySelector('.item-check');
            const qtyInput = row.querySelector('.return-qty');
            const hiddenInputs = row.querySelectorAll('input[type=hidden]');
            const refundEl = row.querySelector('.refund-amount');

            if (check.checked) {
                anyChecked = true;
                qtyInput.disabled = false;
                hiddenInputs.forEach(h => h.disabled = false);
                if (parseInt(qtyInput.value) < 1) qtyInput.value = qtyInput.dataset.max;
                const qty = parseInt(qtyInput.value) || 0;
                const refund = qty * parseFloat(qtyInput.dataset.price);
                total += refund;
                refundEl.textContent = '৳ ' + refund.toFixed(2);
                row.querySelector('.hidden-qty').value = qty;
            } else {
                qtyInput.disabled = true;
                qtyInput.value = 0;
                hiddenInputs.forEach(h => h.disabled = true);
                refundEl.textContent = '৳ 0.00';
            }
        });
        document.getElementById('total-refund').textContent = '৳ ' + total.toFixed(2);
        submitBtn.disabled = !anyChecked;
    }

    checkAll.addEventListener('change', function () {
        rows.forEach(row => {
            const check = row.querySelector('.item-check');
            if (!check.disabled) check.checked = this.checked;   // skip fully-returned rows
        });
        update();
    });

    rows.forEach(row => {
        row.querySelector('.item-check').addEventListener('change', update);
        row.querySelector('.return-qty').addEventListener('input', function () {
            const max = parseInt(this.dataset.max);
            if (parseInt(this.value) > max) this.value = max;
            if (parseInt(this.value) < 1) this.value = 1;
            update();
        });
    });
});
</script>
@endpush
