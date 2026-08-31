@extends('layouts.app')
@section('title', 'POS')
@section('heading', 'Point of Sale')

@push('styles')
<style>
    .pos-products { max-height: calc(100vh - 230px); overflow-y: auto; }
    .pos-card { cursor: pointer; transition: .1s; }
    .pos-card:hover { border-color: #0d6efd; transform: translateY(-2px); }
    .pos-card .ph { height: 90px; background:#f1f3f5; display:flex; align-items:center; justify-content:center; }
    .pos-card .ph img { max-height: 90px; max-width: 100%; object-fit: contain; }
    .cart-wrap { max-height: calc(100vh - 430px); overflow-y: auto; }

    /* On a phone the two columns stack, so viewport-height caps no longer work:
       the grid would fill the screen and push the total and Complete Sale out of
       reach, and calc(100vh - 430px) collapses to nothing on a short screen. */
    @media (max-width: 991.98px) {
        .pos-products { max-height: 42vh; }
        .cart-wrap { max-height: 38vh; }
        .pos-checkout { position: sticky; bottom: 0; z-index: 5; box-shadow: 0 -2px 10px rgba(0,0,0,.08); }
        [data-bs-theme="dark"] .pos-checkout { box-shadow: 0 -2px 10px rgba(0,0,0,.4); }
    }
</style>
@endpush

@section('content')
<div class="row g-3">
    <!-- Products -->
    <div class="col-lg-7">
        <div class="input-group mb-3">
            <span class="input-group-text bg-dark text-white"><i class="bi bi-upc-scan"></i></span>
            <input type="text" id="scan" class="form-control" placeholder="Scan product or batch barcode, then Enter — adds to cart" autofocus>
        </div>
        <input type="text" id="search" class="form-control mb-3" placeholder="🔍 Search product by name / SKU / barcode / model...">
        <div class="row g-2 pos-products" id="product-grid">
            @foreach ($products as $p)
                <div class="col-6 col-md-4 col-xl-3 product-item"
                     data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ $p->sale_price }}"
                     data-stock="{{ $p->stock_quantity }}" data-barcode="{{ $p->barcode }}"
                     data-search="{{ strtolower($p->name . ' ' . $p->sku . ' ' . $p->barcode . ' ' . $p->model) }}">
                    <div class="card pos-card border h-100">
                        <div class="ph">
                            @if ($p->image_url)<img src="{{ $p->image_url }}" alt="">@else<i class="bi bi-box text-muted fs-2"></i>@endif
                        </div>
                        <div class="card-body p-2">
                            <div class="small fw-semibold text-truncate-2" style="min-height:2.4em">{{ $p->name }}</div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <span class="text-primary fw-bold small">৳ {{ number_format($p->sale_price, 2) }}</span>
                                <span class="badge bg-{{ $p->stock_quantity > 0 ? 'light text-dark' : 'danger' }} border">{{ $p->stock_quantity }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Cart -->
    <div class="col-lg-5">
        <form action="{{ route('admin.pos.store') }}" method="POST" id="pos-form">
            @csrf
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="input-group input-group-sm">
                        <select name="customer_id" id="customer-select" class="form-select form-select-sm">
                            <option value="">Walk-in Customer</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} @if($c->phone)({{ $c->phone }})@endif</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newCustomerModal" title="Add new customer">
                            <i class="bi bi-person-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="cart-wrap">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light"><tr><th>Item</th><th style="width:90px">Qty</th><th class="text-end">Total</th><th></th></tr></thead>
                        <tbody id="cart-body">
                            <tr id="cart-empty"><td colspan="4" class="text-center text-muted py-4">Click products to add</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body border-top">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small mb-0">Discount</label>
                            <input type="number" step="0.01" min="0" name="discount" id="discount" value="0" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-0">Tax</label>
                            <input type="number" step="0.01" min="0" name="tax" id="tax" value="0" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><span id="t-subtotal">৳ 0.00</span></div>
                    <div class="d-flex justify-content-between fw-bold fs-4 my-1"><span>Total</span><span id="t-total" data-total="0">৳ 0.00</span></div>
                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <label class="form-label small mb-0">Payment</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile</option>
                                <option value="due">Due</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-0">Paid</label>
                            <input type="number" step="0.01" min="0" name="paid" id="paid" value="0" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2"><span class="text-muted" id="t-change-label">Change / Due</span><span id="t-change">৳ 0.00</span></div>
                </div>
                <div class="card-footer bg-white d-grid gap-2 pos-checkout">
                    <button class="btn btn-success btn-lg" id="checkout-btn"><i class="bi bi-check2-circle me-1"></i>Complete Sale</button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="clear-cart">Clear Cart</button>
                </div>
            </div>
            <div id="hidden-items"></div>
        </form>
    </div>
</div>

<!-- Quick Add Customer -->
<div class="modal fade" id="newCustomerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="customer-error" class="alert alert-danger py-2 d-none"></div>
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" id="cust-name" class="form-control" placeholder="Customer name">
                </div>
                <div class="mb-0">
                    <label class="form-label">Phone</label>
                    <input type="text" id="cust-phone" class="form-control" placeholder="Optional">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-customer" data-url="{{ route('admin.pos.customers.store') }}">
                    <i class="bi bi-check-lg me-1"></i>Save & Select
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cart = {}; // id -> {name, price, qty, stock}
    const body = document.getElementById('cart-body');
    const empty = () => document.getElementById('cart-empty');

    function fmt(n) { return '৳ ' + (Number(n) || 0).toFixed(2); }

    function render() {
        body.querySelectorAll('tr.cart-row').forEach(r => r.remove());
        const ids = Object.keys(cart);
        if (ids.length === 0) {
            if (!empty()) body.insertAdjacentHTML('beforeend', '<tr id="cart-empty"><td colspan="4" class="text-center text-muted py-4">Click products to add</td></tr>');
        } else if (empty()) {
            empty().remove();
        }
        let subtotal = 0;
        ids.forEach(id => {
            const it = cart[id];
            const line = it.price * it.qty;
            subtotal += line;
            const tr = document.createElement('tr');
            tr.className = 'cart-row';
            tr.innerHTML = `<td><div class="small fw-semibold">${it.name}</div><div class="text-muted" style="font-size:.72rem">${fmt(it.price)}</div></td>
                <td><input type="number" min="1" max="${it.stock}" value="${it.qty}" data-id="${id}" class="form-control form-control-sm cart-qty"></td>
                <td class="text-end">${fmt(line)}</td>
                <td><button type="button" class="btn btn-sm text-danger p-0 cart-remove" data-id="${id}"><i class="bi bi-x-lg"></i></button></td>`;
            body.appendChild(tr);
        });
        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const tax = parseFloat(document.getElementById('tax').value) || 0;
        const total = Math.max(subtotal - discount + tax, 0);
        document.getElementById('t-subtotal').textContent = fmt(subtotal);
        const totalEl = document.getElementById('t-total');
        totalEl.textContent = fmt(total);
        totalEl.dataset.total = total;
        const paid = parseFloat(document.getElementById('paid').value) || 0;
        const diff = paid - total;
        const changeEl = document.getElementById('t-change');
        const labelEl = document.getElementById('t-change-label');
        if (diff < 0) {
            // Customer still owes money.
            if (labelEl) labelEl.textContent = 'Due';
            changeEl.textContent = fmt(-diff);
            changeEl.style.color = '#dc3545';
        } else {
            // Exact or overpaid — show change to return.
            if (labelEl) labelEl.textContent = 'Change';
            changeEl.textContent = fmt(diff);
            changeEl.style.color = '#198754';
        }
    }

    function addToCart(id, name, price, stock) {
        if (stock <= 0) { alert(name + ' is out of stock.'); return; }
        if (cart[id]) {
            if (cart[id].qty < stock) cart[id].qty++;
            else { alert('Only ' + stock + ' in stock.'); }
        } else {
            cart[id] = { name, price: parseFloat(price), qty: 1, stock: parseInt(stock) };
        }
        render();
    }

    document.getElementById('product-grid').addEventListener('click', function (e) {
        const item = e.target.closest('.product-item');
        if (!item) return;
        addToCart(item.dataset.id, item.dataset.name, item.dataset.price, item.dataset.stock);
    });

    body.addEventListener('input', function (e) {
        if (e.target.classList.contains('cart-qty')) {
            const id = e.target.dataset.id;
            let v = parseInt(e.target.value) || 1;
            if (v < 1) v = 1;
            if (v > cart[id].stock) { v = cart[id].stock; alert('Only ' + cart[id].stock + ' in stock.'); }
            cart[id].qty = v;
            render();
        }
    });
    body.addEventListener('click', function (e) {
        const btn = e.target.closest('.cart-remove');
        if (btn) { delete cart[btn.dataset.id]; render(); }
    });

    ['discount', 'tax', 'paid'].forEach(id => document.getElementById(id).addEventListener('input', render));

    document.getElementById('clear-cart').addEventListener('click', function () {
        Object.keys(cart).forEach(k => delete cart[k]);
        render();
    });

    // Batch number -> product id (scanned batch barcodes resolve to their product)
    const batchMap = @json($batchMap);

    function findProductEl(code) {
        const lc = code.toLowerCase();
        // 1) direct product barcode / SKU / search match
        let el = [...document.querySelectorAll('.product-item')].find(p =>
            p.dataset.barcode === code || (p.dataset.search || '').split(' ').includes(lc));
        if (el) return el;
        // 2) batch barcode -> product id (case-insensitive)
        let pid = batchMap[code];
        if (pid === undefined) {
            const key = Object.keys(batchMap).find(k => k.toLowerCase() === lc);
            if (key) pid = batchMap[key];
        }
        if (pid !== undefined) {
            return document.querySelector('.product-item[data-id="' + pid + '"]');
        }
        return null;
    }

    // Barcode scan: match by product barcode/SKU or batch number, then add to cart
    const scan = document.getElementById('scan');
    scan.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const code = this.value.trim();
        if (!code) return;
        const el = findProductEl(code);
        if (el) {
            addToCart(el.dataset.id, el.dataset.name, el.dataset.price, el.dataset.stock);
        } else {
            alert('No product or batch found for: ' + code);
        }
        this.value = '';
        this.focus();
    });

    // Search filter (also matches batch numbers -> their product)
    document.getElementById('search').addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();

        // Products whose batch number matches the query.
        const batchPids = new Set();
        if (q) {
            for (const bno in batchMap) {
                if (bno.toLowerCase().includes(q)) batchPids.add(String(batchMap[bno]));
            }
        }

        document.querySelectorAll('.product-item').forEach(el => {
            const match = el.dataset.search.includes(q) || batchPids.has(el.dataset.id);
            el.style.display = match ? '' : 'none';
        });
    });

    // Quick add customer (AJAX) → prepend to select and choose it
    const saveCustBtn = document.getElementById('save-customer');
    const token = document.querySelector('meta[name="csrf-token"]').content;
    saveCustBtn.addEventListener('click', function () {
        const name = document.getElementById('cust-name').value.trim();
        const phone = document.getElementById('cust-phone').value.trim();
        const err = document.getElementById('customer-error');
        err.classList.add('d-none');
        if (!name) { err.textContent = 'Name is required.'; err.classList.remove('d-none'); return; }

        saveCustBtn.disabled = true;
        fetch(this.dataset.url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ name, phone }),
        })
        .then(r => r.ok ? r.json() : r.json().then(j => Promise.reject(j)))
        .then(c => {
            const sel = document.getElementById('customer-select');
            const opt = new Option((c.phone ? c.name + ' (' + c.phone + ')' : c.name), c.id, true, true);
            sel.add(opt, sel.options[1] || null);
            document.getElementById('cust-name').value = '';
            document.getElementById('cust-phone').value = '';
            bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();
        })
        .catch(j => {
            err.textContent = (j && j.message) ? j.message : 'Could not save customer.';
            err.classList.remove('d-none');
        })
        .finally(() => { saveCustBtn.disabled = false; });
    });

    // Build hidden inputs on submit
    document.getElementById('pos-form').addEventListener('submit', function (e) {
        const ids = Object.keys(cart);
        if (ids.length === 0) { e.preventDefault(); alert('Cart is empty.'); return; }
        const wrap = document.getElementById('hidden-items');
        wrap.innerHTML = '';
        ids.forEach((id, i) => {
            wrap.insertAdjacentHTML('beforeend',
                `<input type="hidden" name="items[${i}][product_id]" value="${id}">
                 <input type="hidden" name="items[${i}][quantity]" value="${cart[id].qty}">
                 <input type="hidden" name="items[${i}][unit_price]" value="${cart[id].price}">`);
        });
    });
});
</script>
@endpush
