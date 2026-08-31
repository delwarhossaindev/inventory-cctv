@csrf
@php $product = $product ?? new \App\Models\Product(); @endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Basic Information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" value="{{ old('model', $product->model) }}" class="form-control">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="form-control" placeholder="auto-generated from name if empty">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" @selected(old('status', $product->status) === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Pricing & Inventory</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Barcode</label>
                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="form-control" placeholder="blank = auto-generate (EAN-13)">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Purchase Price</label>
                        <input type="number" step="0.01" min="0" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? 0) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sale Price</label>
                        <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product->sale_price ?? 0) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        @if ($product->exists)
                            <label class="form-label">Current Stock</label>
                            <div class="form-control bg-light d-flex justify-content-between align-items-center">
                                <span>{{ $product->stock_quantity }} {{ $product->unit }}</span>
                                <a href="{{ route('admin.stock.adjust', $product) }}" class="small">Adjust</a>
                            </div>
                        @else
                            <label class="form-label">Opening Stock</label>
                            <input type="number" min="0" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" class="form-control">
                        @endif
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Low Stock Alert At</label>
                        <input type="number" min="0" name="alert_quantity" value="{{ old('alert_quantity', $product->alert_quantity ?? 0) }}" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit" value="{{ old('unit', $product->unit ?? 'pcs') }}" class="form-control" placeholder="pcs">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Warranty (days)</label>
                        <input type="number" min="0" name="warranty_days" value="{{ old('warranty_days', $product->warranty_days) }}" class="form-control" placeholder="0 = no warranty">
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Content (HTML allowed)</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Short Description</label>
                    <textarea name="short_description" rows="3" class="form-control richtext">{{ old('short_description', $product->short_description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="5" class="form-control richtext">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Advantages</label>
                    <textarea name="advantages" rows="4" class="form-control richtext">{{ old('advantages', $product->advantages) }}</textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label">Specifications</label>
                    <textarea name="specifications" rows="4" class="form-control richtext">{{ old('specifications', $product->specifications) }}</textarea>
                </div>
            </div>
        </div>

    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Categorization</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Main Category</label>
                    <select name="main_category_id" id="main_category" class="form-select" data-children-url="{{ route('admin.categories.children') }}">
                        <option value="">— Select —</option>
                        @foreach ($mains as $m)
                            <option value="{{ $m->id }}" @selected(old('main_category_id', $product->main_category_id) == $m->id)>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" id="category" class="form-select">
                        <option value="">— Select —</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">Sub Category</label>
                    <select name="sub_category_id" id="sub_category" class="form-select">
                        <option value="">— Select —</option>
                        @foreach ($subCategories as $s)
                            <option value="{{ $s->id }}" @selected(old('sub_category_id', $product->sub_category_id) == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">Main Image</div>
            <div class="card-body">
                <input type="url" name="image_url" id="image_url" value="{{ old('image_url', $product->image_url) }}" class="form-control mb-3" placeholder="https://...">
                <img src="{{ $product->image_url ?: '' }}" id="image-preview" class="img-fluid rounded border {{ $product->image_url ? '' : 'd-none' }}" alt="preview">
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">SEO</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" value="{{ old('meta_title', $product->meta_title) }}" class="form-control">
                </div>
                <div class="mb-0">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" rows="3" class="form-control">{{ old('meta_description', $product->meta_description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Gallery Images</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-gallery"><i class="bi bi-plus-lg"></i> Add URL</button>
            </div>
            <div class="card-body" id="gallery-wrap">
                @php $gallery = old('gallery_images', $product->gallery_images ?? []); @endphp
                @forelse ($gallery as $url)
                    <div class="input-group mb-2 gallery-row">
                        <input type="url" name="gallery_images[]" value="{{ $url }}" class="form-control" placeholder="https://...">
                        <button type="button" class="btn btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                    </div>
                @empty
                    <div class="input-group mb-2 gallery-row">
                        <input type="url" name="gallery_images[]" class="form-control" placeholder="https://...">
                        <button type="button" class="btn btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">FAQs</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-faq"><i class="bi bi-plus-lg"></i> Add FAQ</button>
            </div>
            <div class="card-body" id="faq-wrap">
                @php $faqs = old('faqs', $product->faqs ?? []); @endphp
                @foreach ($faqs as $i => $faq)
                    <div class="border rounded p-3 mb-2 faq-row">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">FAQ</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <input type="text" name="faqs[{{ $i }}][question]" value="{{ $faq['question'] ?? '' }}" class="form-control mb-2" placeholder="Question">
                        <textarea name="faqs[{{ $i }}][answer]" rows="2" class="form-control" placeholder="Answer">{{ $faq['answer'] ?? '' }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Product</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Rich text editor for the Content fields. GPL build via jsdelivr — no API key needed.
    if (window.tinymce) {
        tinymce.init({
            selector: 'textarea.richtext',
            license_key: 'gpl',
            height: 260,
            menubar: false,
            branding: false,
            plugins: 'lists link table code autolink',
            toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link table | removeformat | code',
            content_style: 'body{font-family:system-ui,sans-serif;font-size:14px}',
        });
    }

    const childrenUrl = document.getElementById('main_category').dataset.childrenUrl;

    function loadChildren(parentId, target, selected = null) {
        target.innerHTML = '<option value="">— Select —</option>';
        if (!parentId) return Promise.resolve();
        return fetch(`${childrenUrl}?parent_id=${parentId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(items => {
                items.forEach(i => {
                    const opt = new Option(i.name, i.id);
                    if (selected && String(selected) === String(i.id)) opt.selected = true;
                    target.add(opt);
                });
            });
    }

    const main = document.getElementById('main_category');
    const cat = document.getElementById('category');
    const sub = document.getElementById('sub_category');

    main.addEventListener('change', function () {
        loadChildren(this.value, cat).then(() => { sub.innerHTML = '<option value="">— Select —</option>'; });
    });
    cat.addEventListener('change', function () {
        loadChildren(this.value, sub);
    });

    // Main image live preview
    const imgInput = document.getElementById('image_url');
    const preview = document.getElementById('image-preview');
    imgInput.addEventListener('input', function () {
        if (this.value) { preview.src = this.value; preview.classList.remove('d-none'); }
        else { preview.classList.add('d-none'); }
    });

    // Gallery repeater
    document.getElementById('add-gallery').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'input-group mb-2 gallery-row';
        row.innerHTML = `<input type="url" name="gallery_images[]" class="form-control" placeholder="https://...">
            <button type="button" class="btn btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>`;
        document.getElementById('gallery-wrap').appendChild(row);
    });

    // FAQ repeater (use a running index so question/answer group into the same array entry)
    let faqIndex = document.querySelectorAll('#faq-wrap .faq-row').length;
    document.getElementById('add-faq').addEventListener('click', function () {
        const i = faqIndex++;
        const row = document.createElement('div');
        row.className = 'border rounded p-3 mb-2 faq-row';
        row.innerHTML = `<div class="d-flex justify-content-between mb-2"><span class="text-muted small">FAQ</span>
            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button></div>
            <input type="text" name="faqs[${i}][question]" class="form-control mb-2" placeholder="Question">
            <textarea name="faqs[${i}][answer]" rows="2" class="form-control" placeholder="Answer"></textarea>`;
        document.getElementById('faq-wrap').appendChild(row);
    });

    // Remove rows (event delegation)
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-row');
        if (btn) btn.closest('.gallery-row, .faq-row').remove();
    });
});
</script>
@endpush
