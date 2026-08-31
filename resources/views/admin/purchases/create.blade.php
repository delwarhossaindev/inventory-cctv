@extends('layouts.app')
@section('title', 'New Purchase')
@section('heading', 'New Purchase')

@section('content')
<form action="{{ route('admin.purchases.store') }}" method="POST" id="purchase-form">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Items</span>
                    <div style="min-width:320px">
                        <select id="product-picker" class="form-select form-select-sm">
                            <option value="">+ Add product to purchase...</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" data-cost="{{ $p->purchase_price }}" data-name="{{ $p->name }}" data-sku="{{ $p->sku }}">
                                    {{ $p->name }} @if($p->sku)({{ $p->sku }})@endif — stock: {{ $p->stock_quantity }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle" id="items-table">
                        <thead class="table-light">
                            <tr><th>Product</th><th style="width:110px">Qty</th><th style="width:150px">Unit Cost</th><th style="width:150px" class="text-end">Subtotal</th><th style="width:40px"></th></tr>
                        </thead>
                        <tbody id="items-body">
                            <tr id="empty-row"><td colspan="5" class="text-center text-muted py-4">No items added.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Purchase Info</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="received">Received (add to stock now)</option>
                            <option value="pending">Pending (no stock change)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Note</label>
                        <textarea name="note" rows="2" class="form-control">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Totals</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Subtotal</span><span id="t-subtotal">৳ 0.00</span></div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Discount</span>
                        <input type="number" step="0.01" min="0" name="discount" id="discount" value="{{ old('discount', 0) }}" class="form-control form-control-sm text-end" style="width:120px">
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Tax</span>
                        <input type="number" step="0.01" min="0" name="tax" id="tax" value="{{ old('tax', 0) }}" class="form-control form-control-sm text-end" style="width:120px">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2 fw-bold fs-5"><span>Total</span><span id="t-total">৳ 0.00</span></div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Paid</span>
                        <input type="number" step="0.01" min="0" name="paid" id="paid" value="{{ old('paid', 0) }}" class="form-control form-control-sm text-end" style="width:120px">
                    </div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Due</span><span id="t-due">৳ 0.00</span></div>
                </div>
            </div>

            <button class="btn btn-primary w-100" id="submit-btn"><i class="bi bi-check-lg me-1"></i>Save Purchase</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.getElementById('items-body');
    const emptyRow = document.getElementById('empty-row');
    const picker = document.getElementById('product-picker');
    let idx = 0;

    function fmt(n) { return '৳ ' + (Number(n) || 0).toFixed(2); }

    function recalc() {
        let subtotal = 0;
        body.querySelectorAll('tr.item-row').forEach(function (row) {
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const cost = parseFloat(row.querySelector('.cost').value) || 0;
            const sub = qty * cost;
            row.querySelector('.line-sub').textContent = fmt(sub);
            subtotal += sub;
        });
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const total = subtotal - discount + tax;
        const paid = parseFloat(document.getElementById('paid').value) || 0;
        document.getElementById('t-subtotal').textContent = fmt(subtotal);
        document.getElementById('t-total').textContent = fmt(total);
        document.getElementById('t-due').textContent = fmt(Math.max(total - paid, 0));
    }

    function addRow(id, name, sku, cost) {
        if (emptyRow) emptyRow.remove();
        const i = idx++;
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td>${name} ${sku ? '<span class="text-muted small">('+sku+')</span>' : ''}
                <input type="hidden" name="items[${i}][product_id]" value="${id}"></td>
            <td><input type="number" min="1" value="1" class="form-control form-control-sm qty" name="items[${i}][quantity]"></td>
            <td><input type="number" step="0.01" min="0" value="${Number(cost).toFixed(2)}" class="form-control form-control-sm cost" name="items[${i}][unit_cost]"></td>
            <td class="text-end line-sub">৳ 0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-x-lg"></i></button></td>`;
        body.appendChild(tr);
        recalc();
    }

    picker.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (!this.value) return;
        addRow(this.value, opt.dataset.name, opt.dataset.sku, opt.dataset.cost);
        this.value = '';
    });

    body.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-item');
        if (btn) {
            btn.closest('tr').remove();
            if (!body.querySelector('tr.item-row')) body.innerHTML = '<tr id="empty-row"><td colspan="5" class="text-center text-muted py-4">No items added.</td></tr>';
            recalc();
        }
    });

    body.addEventListener('input', function (e) {
        if (e.target.classList.contains('qty') || e.target.classList.contains('cost')) recalc();
    });
    ['discount', 'tax', 'paid'].forEach(id => document.getElementById(id).addEventListener('input', recalc));

    document.getElementById('purchase-form').addEventListener('submit', function (e) {
        if (!body.querySelector('tr.item-row')) { e.preventDefault(); alert('Add at least one product.'); }
    });
});
</script>
@endpush
