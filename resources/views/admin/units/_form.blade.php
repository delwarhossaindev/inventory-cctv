@csrf
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $unit->name) }}" class="form-control" required autofocus placeholder="e.g. Piece">
                </div>

                <div class="mb-3">
                    <label class="form-label">Short Name</label>
                    <input type="text" name="short_name" value="{{ old('short_name', $unit->short_name) }}" class="form-control" placeholder="e.g. pcs">
                </div>

                <div class="mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="active" @selected(old('status', $unit->status ?? 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $unit->status) === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save</button>
                    <a href="{{ route('admin.units.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
