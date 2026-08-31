@csrf
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $supplier->name) }}" class="form-control" required autofocus>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company</label>
                        <input type="text" name="company" value="{{ old('company', $supplier->company) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" rows="2" class="form-control">{{ old('address', $supplier->address) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" @selected(old('status', $supplier->status ?? 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status', $supplier->status) === 'inactive')>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save</button>
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
