@extends('layouts.app')
@section('title', 'New Quotation')
@section('heading', 'New Quotation')

@section('content')
<form action="{{ route('admin.quotations.store') }}" method="POST" id="quote-form">
    @csrf
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Customer</label>
                    <select name="customer_id" class="form-select form-select-sm">
                        <option value="">Walk-in</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date</label>
                    <input type="date" name="quote_date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Valid Until</label>
                    <input type="date" name="valid_until" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Discount</label>
                    <input type="number" step="0.01" min="0" name="discount" value="0" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tax</label>
                    <input type="number" step="0.01" min="0" name="tax" value="0" class="form-control form-control-sm">
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Items</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm" id="items-table">
                    <thead class="table-light">
                        <tr><th style="min-width:200px">Product</th><th style="width:100px">Qty</th><th style="width:140px">Unit Price</th><th style="width:120px" class="text-end">Subtotal</th><th style="width:40px"></th></tr>
                    </thead>
                    <tbody id="items-body"></tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="add-row"><i class="bi bi-plus-lg me-1"></i>Add Item</button>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label">Note</label>
            <input type="text" name="note" class="form-control form-control-sm" placeholder="Optional">
        </div>
        <div class="col-md-9 text-end pt-4">
            <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Quotation</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const products = @json($products);
    const body = document.getElementById('items-body');
    let idx = 0;

    function addRow() {
        const i = idx++;
        const opts = products.map(p => `<option value="${p.id}" data-price="${p.sale_price}">${p.name} (${p.sku || ''})</option>`).join('');
        const tr = document.createElement('tr');
        tr.innerHTML = `<td><select name="items[${i}][product_id]" class="form-select form-select-sm prod-sel" required><option value="">Select</option>${opts}</select></td>
            <td><input type="number" name="items[${i}][quantity]" min="1" value="1" class="form-control form-control-sm qty" required></td>
            <td><input type="number" name="items[${i}][unit_price]" step="0.01" min="0" value="0" class="form-control form-control-sm price" required></td>
            <td class="text-end fw-semibold line-total">৳ 0.00</td>
            <td><button type="button" class="btn btn-sm text-danger p-0 remove-row"><i class="bi bi-x-lg"></i></button></td>`;
        body.appendChild(tr);

        tr.querySelector('.prod-sel').addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            tr.querySelector('.price').value = parseFloat(opt.dataset.price || 0).toFixed(2);
            calc(tr);
        });
        tr.querySelector('.qty').addEventListener('input', () => calc(tr));
        tr.querySelector('.price').addEventListener('input', () => calc(tr));
        tr.querySelector('.remove-row').addEventListener('click', () => { tr.remove(); });
    }

    function calc(tr) {
        const q = parseInt(tr.querySelector('.qty').value) || 0;
        const p = parseFloat(tr.querySelector('.price').value) || 0;
        tr.querySelector('.line-total').textContent = '৳ ' + (q * p).toFixed(2);
    }

    document.getElementById('add-row').addEventListener('click', addRow);
    addRow();
});
</script>
@endpush
