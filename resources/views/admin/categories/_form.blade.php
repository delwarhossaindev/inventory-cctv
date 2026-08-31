@csrf
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">Level <span class="text-danger">*</span></label>
                    <select name="level" id="level" class="form-select" required>
                        @foreach (\App\Models\Category::LEVELS as $val => $label)
                            <option value="{{ $val }}" @selected(old('level', $category->level) == $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3" id="parent-wrap">
                    <label class="form-label">Parent</label>
                    <select name="parent_id" id="parent_id" class="form-select"
                            data-mains='@json($mains->map->only("id","name"))'
                            data-cats='@json($cats->map->only("id","name"))'
                            data-current="{{ old('parent_id', $category->parent_id) }}">
                        <option value="">— Select —</option>
                    </select>
                    <div class="form-text">Main Category has no parent. Category &rarr; pick a Main. Sub Category &rarr; pick a Category.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" @selected(old('status', $category->status ?? 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $category->status) === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save</button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const level = document.getElementById('level');
    const parentWrap = document.getElementById('parent-wrap');
    const parent = document.getElementById('parent_id');
    const mains = JSON.parse(parent.dataset.mains || '[]');
    const cats = JSON.parse(parent.dataset.cats || '[]');
    const current = parent.dataset.current;

    function render() {
        const lvl = parseInt(level.value, 10);
        parent.innerHTML = '<option value="">— Select —</option>';
        if (lvl === 1) { parentWrap.style.display = 'none'; return; }
        parentWrap.style.display = '';
        const list = lvl === 2 ? mains : cats;
        list.forEach(i => {
            const opt = new Option(i.name, i.id);
            if (current && String(current) === String(i.id)) opt.selected = true;
            parent.add(opt);
        });
    }

    level.addEventListener('change', render);
    render();
});
</script>
@endpush
