@extends('layouts.app')
@section('title', 'POS 2')
@section('heading', 'Point of Sale 2')

@push('styles')
<style>
    .pos2-left  { max-height: calc(100vh - 160px); display:flex; flex-direction:column; }
    .pos2-results { flex:1; overflow-y:auto; }
    .cart-wrap  { max-height: calc(100vh - 430px); overflow-y: auto; }

    .p2-card { cursor:pointer; transition:.12s; border:1px solid #dee2e6; }
    .p2-card:hover { border-color:#0d6efd; transform:translateY(-2px); box-shadow:0 4px 12px rgba(13,110,253,.15); }
    .p2-card .p2-img { height:90px; background:#f8f9fa; display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .p2-card .p2-img img { max-height:90px; max-width:100%; object-fit:contain; }

    #search2:focus { border-color:#0d6efd; box-shadow:0 0 0 .2rem rgba(13,110,253,.15); }

    /* On a phone the two columns stack, so viewport-height caps no longer work:
       the results panel would fill the screen and push the total and Complete Sale
       out of reach, and calc(100vh - 430px) collapses to nothing on a short screen. */
    @media (max-width: 991.98px) {
        .pos2-left { max-height: none; }
        .pos2-results { max-height: 42vh; }
        .cart-wrap { max-height: 38vh; }
        .pos-checkout { position: sticky; bottom: 0; z-index: 5; box-shadow: 0 -2px 10px rgba(0,0,0,.08); }
        [data-bs-theme="dark"] .pos-checkout { box-shadow: 0 -2px 10px rgba(0,0,0,.4); }
    }
</style>
@endpush

@section('content')
<div class="row g-3">

    {{-- Left: Search Panel --}}
    <div class="col-lg-7">
        <div class="pos2-left">

            {{-- Barcode Scan --}}
            <div class="input-group mb-2">
                <span class="input-group-text bg-dark text-white"><i class="bi bi-upc-scan"></i></span>
                <input type="text" id="scan2" class="form-control"
                       placeholder="Scan barcode or batch number, then Enter — adds to cart" autofocus>
            </div>

            {{-- Text Search --}}
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="search2" class="form-control form-control-lg"
                       placeholder="Search product by name / SKU / barcode / model...">
                <button class="btn btn-outline-secondary" type="button" id="clear-search2" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            {{-- Results area --}}
            <div class="pos2-results" id="results-wrap">

                {{-- Placeholder (shown when no search) --}}
                <div id="p2-placeholder" class="text-center text-muted py-5">
                    <i class="bi bi-search display-4 d-block mb-3 opacity-25"></i>
                    <div class="fw-semibold fs-5">Search for products</div>
                    <div class="small">Type a name, SKU, barcode or scan to find products</div>
                </div>

                {{-- Spinner --}}
                <div id="p2-spinner" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>

                {{-- No results --}}
                <div id="p2-empty" class="text-center text-muted py-5 d-none">
                    <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>
                    <div class="fw-semibold">No products found</div>
                </div>

                {{-- Product cards (populated by JS) --}}
                <div class="row g-2" id="p2-grid"></div>

            </div>
        </div>
    </div>

    {{-- Right: Cart --}}
    <div class="col-lg-5">
        <form action="{{ route('admin.pos.store') }}" method="POST" id="pos2-form">
            @csrf
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="input-group input-group-sm">
                        <select name="customer_id" id="customer-select2" class="form-select form-select-sm">
                            <option value="">Walk-in Customer</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}@if($c->phone) ({{ $c->phone }})@endif</option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#newCustomerModal2" title="Add new customer">
                            <i class="bi bi-person-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="cart-wrap">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr><th>Item</th><th style="width:90px">Qty</th><th class="text-end">Total</th><th></th></tr>
                        </thead>
                        <tbody id="cart2-body">
                            <tr id="cart2-empty">
                                <td colspan="4" class="text-center text-muted py-4">Click products to add</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-body border-top">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small mb-0">Discount</label>
                            <input type="number" step="0.01" min="0" name="discount" id="discount2" value="0" class="form-control form-control-sm">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-0">Tax</label>
                            <input type="number" step="0.01" min="0" name="tax" id="tax2" value="0" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Subtotal</span><span id="t2-subtotal">৳ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-4 my-1">
                        <span>Total</span><span id="t2-total" data-total="0">৳ 0.00</span>
                    </div>
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
                            <input type="number" step="0.01" min="0" name="paid" id="paid2" value="0" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-muted" id="t2-change-label">Change / Due</span>
                        <span id="t2-change">৳ 0.00</span>
                    </div>
                </div>

                <div class="card-footer bg-white d-grid gap-2 pos-checkout">
                    <button class="btn btn-success btn-lg" id="checkout2-btn">
                        <i class="bi bi-check2-circle me-1"></i>Complete Sale
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="clear-cart2">Clear Cart</button>
                </div>
            </div>
            <div id="hidden2-items"></div>
        </form>
    </div>
</div>

{{-- Quick Add Customer Modal --}}
<div class="modal fade" id="newCustomerModal2" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>New Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="c2-error" class="alert alert-danger py-2 d-none"></div>
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" id="c2-name" class="form-control" placeholder="Customer name">
                </div>
                <div class="mb-0">
                    <label class="form-label">Phone</label>
                    <input type="text" id="c2-phone" class="form-control" placeholder="Optional">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="c2-save"
                        data-url="{{ route('admin.pos.customers.store') }}">
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
    const SEARCH_URL = '{{ route('admin.pos.search') }}';
    const token      = document.querySelector('meta[name="csrf-token"]').content;

    /* ───── Cart State ───── */
    const cart = {};
    const cartBody = document.getElementById('cart2-body');

    function fmt(n) { return '৳ ' + (Number(n) || 0).toFixed(2); }

    function emptyRow() { return document.getElementById('cart2-empty'); }

    function renderCart() {
        cartBody.querySelectorAll('tr.cart-row').forEach(r => r.remove());
        const ids = Object.keys(cart);
        if (ids.length === 0) {
            if (!emptyRow()) {
                cartBody.insertAdjacentHTML('beforeend',
                    '<tr id="cart2-empty"><td colspan="4" class="text-center text-muted py-4">Click products to add</td></tr>');
            }
        } else {
            emptyRow()?.remove();
        }
        let subtotal = 0;
        ids.forEach(id => {
            const it = cart[id];
            const line = it.price * it.qty;
            subtotal += line;
            const tr = document.createElement('tr');
            tr.className = 'cart-row';
            tr.innerHTML =
                `<td><div class="small fw-semibold">${it.name}</div>` +
                `<div class="text-muted" style="font-size:.72rem">${fmt(it.price)}</div></td>` +
                `<td><input type="number" min="1" max="${it.stock}" value="${it.qty}" data-id="${id}" class="form-control form-control-sm cart2-qty"></td>` +
                `<td class="text-end">${fmt(line)}</td>` +
                `<td><button type="button" class="btn btn-sm text-danger p-0 cart2-remove" data-id="${id}"><i class="bi bi-x-lg"></i></button></td>`;
            cartBody.appendChild(tr);
        });
        const discount = parseFloat(document.getElementById('discount2').value) || 0;
        const tax      = parseFloat(document.getElementById('tax2').value) || 0;
        const total    = Math.max(subtotal - discount + tax, 0);
        document.getElementById('t2-subtotal').textContent = fmt(subtotal);
        const totalEl = document.getElementById('t2-total');
        totalEl.textContent = fmt(total);
        totalEl.dataset.total = total;
        const paid  = parseFloat(document.getElementById('paid2').value) || 0;
        const diff  = paid - total;
        const chEl  = document.getElementById('t2-change');
        const lblEl = document.getElementById('t2-change-label');
        if (diff < 0) {
            lblEl.textContent  = 'Due';
            chEl.textContent   = fmt(-diff);
            chEl.style.color   = '#dc3545';
        } else {
            lblEl.textContent  = 'Change';
            chEl.textContent   = fmt(diff);
            chEl.style.color   = '#198754';
        }
    }

    function addToCart(id, name, price, stock) {
        id = String(id);
        if (parseInt(stock) <= 0) { alert(name + ' is out of stock.'); return; }
        if (cart[id]) {
            if (cart[id].qty < parseInt(stock)) { cart[id].qty++; }
            else { alert('Only ' + stock + ' in stock.'); }
        } else {
            cart[id] = { name, price: parseFloat(price), qty: 1, stock: parseInt(stock) };
        }
        renderCart();
    }

    cartBody.addEventListener('input', function (e) {
        if (!e.target.classList.contains('cart2-qty')) return;
        const id = e.target.dataset.id;
        let v = parseInt(e.target.value) || 1;
        if (v < 1) v = 1;
        if (v > cart[id].stock) { v = cart[id].stock; alert('Only ' + cart[id].stock + ' in stock.'); }
        cart[id].qty = v;
        renderCart();
    });

    cartBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.cart2-remove');
        if (btn) { delete cart[btn.dataset.id]; renderCart(); }
    });

    ['discount2', 'tax2', 'paid2'].forEach(id =>
        document.getElementById(id).addEventListener('input', renderCart));

    document.getElementById('clear-cart2').addEventListener('click', function () {
        Object.keys(cart).forEach(k => delete cart[k]);
        renderCart();
    });

    /* ───── Product Search (AJAX) ───── */
    const grid        = document.getElementById('p2-grid');
    const placeholder = document.getElementById('p2-placeholder');
    const spinner     = document.getElementById('p2-spinner');
    const noResults   = document.getElementById('p2-empty');
    let   debounce    = null;

    function showState(state) {
        placeholder.classList.toggle('d-none', state !== 'placeholder');
        spinner.classList.toggle('d-none',     state !== 'spinner');
        noResults.classList.toggle('d-none',   state !== 'empty');
    }

    function renderProducts(products) {
        grid.innerHTML = '';
        if (products.length === 0) { showState('empty'); return; }
        showState(null);
        products.forEach(p => {
            const inStock = p.stock_quantity > 0;
            const imgHtml = p.image_url
                ? `<img src="${p.image_url}" alt="">`
                : `<i class="bi bi-box text-muted fs-2"></i>`;
            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 col-xl-3';
            col.innerHTML =
                `<div class="card p2-card h-100 p2-item"
                      data-id="${p.id}" data-name="${p.name}"
                      data-price="${p.sale_price}" data-stock="${p.stock_quantity}">
                    <div class="p2-img">${imgHtml}</div>
                    <div class="card-body p-2">
                        <div class="small fw-semibold" style="min-height:2.4em;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">${p.name}</div>
                        <div class="d-flex justify-content-between align-items-center mt-1 gap-1">
                            <span class="text-primary fw-bold small">৳ ${parseFloat(p.sale_price).toLocaleString('en-BD', {minimumFractionDigits:2})}</span>
                            <span class="badge ${inStock ? 'bg-light text-dark border' : 'bg-danger text-white'}"
                                  title="Stock">
                                <i class="bi bi-boxes me-1" style="font-size:.65rem"></i>${p.stock_quantity}
                            </span>
                        </div>
                    </div>
                </div>`;
            grid.appendChild(col);
        });
    }

    function doSearch(q) {
        if (!q) { showState('placeholder'); grid.innerHTML = ''; return; }
        showState('spinner'); grid.innerHTML = '';
        fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(products => renderProducts(products))
            .catch(() => showState('empty'));
    }

    grid.addEventListener('click', function (e) {
        const card = e.target.closest('.p2-item');
        if (!card) return;
        addToCart(card.dataset.id, card.dataset.name, card.dataset.price, card.dataset.stock);
    });

    const searchInput = document.getElementById('search2');
    searchInput.addEventListener('input', function () {
        const q = this.value.trim();
        clearTimeout(debounce);
        debounce = setTimeout(() => doSearch(q), 280);
    });

    document.getElementById('clear-search2').addEventListener('click', function () {
        searchInput.value = '';
        grid.innerHTML = '';
        showState('placeholder');
        searchInput.focus();
    });

    /* ───── Barcode Scan ───── */
    document.getElementById('scan2').addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const code = this.value.trim();
        if (!code) return;
        this.value = '';
        this.focus();
        fetch(`${SEARCH_URL}?q=${encodeURIComponent(code)}`)
            .then(r => r.json())
            .then(products => {
                if (products.length === 1) {
                    addToCart(products[0].id, products[0].name, products[0].sale_price, products[0].stock_quantity);
                } else if (products.length > 1) {
                    renderProducts(products);
                    searchInput.value = code;
                    showState(null);
                } else {
                    alert('No product found for: ' + code);
                }
            });
    });

    /* ───── Quick Add Customer ───── */
    document.getElementById('c2-save').addEventListener('click', function () {
        const name  = document.getElementById('c2-name').value.trim();
        const phone = document.getElementById('c2-phone').value.trim();
        const err   = document.getElementById('c2-error');
        err.classList.add('d-none');
        if (!name) { err.textContent = 'Name is required.'; err.classList.remove('d-none'); return; }
        this.disabled = true;
        fetch(this.dataset.url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ name, phone }),
        })
        .then(r => r.ok ? r.json() : r.json().then(j => Promise.reject(j)))
        .then(c => {
            const sel = document.getElementById('customer-select2');
            const opt = new Option((c.phone ? c.name + ' (' + c.phone + ')' : c.name), c.id, true, true);
            sel.add(opt, sel.options[1] || null);
            document.getElementById('c2-name').value = '';
            document.getElementById('c2-phone').value = '';
            bootstrap.Modal.getInstance(document.getElementById('newCustomerModal2')).hide();
        })
        .catch(j => {
            err.textContent = (j && j.message) ? j.message : 'Could not save customer.';
            err.classList.remove('d-none');
        })
        .finally(() => { this.disabled = false; });
    });

    /* ───── Form Submit ───── */
    document.getElementById('pos2-form').addEventListener('submit', function (e) {
        const ids = Object.keys(cart);
        if (ids.length === 0) { e.preventDefault(); alert('Cart is empty.'); return; }
        const wrap = document.getElementById('hidden2-items');
        wrap.innerHTML = '';
        ids.forEach((id, i) => {
            wrap.insertAdjacentHTML('beforeend',
                `<input type="hidden" name="items[${i}][product_id]" value="${id}">` +
                `<input type="hidden" name="items[${i}][quantity]" value="${cart[id].qty}">` +
                `<input type="hidden" name="items[${i}][unit_price]" value="${cart[id].price}">`);
        });
    });
});
</script>
@endpush
