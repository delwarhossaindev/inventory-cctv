@csrf
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Permission Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $permission->name) }}" class="form-control" required autofocus
                           placeholder="e.g. export reports">
                    <div class="form-text">Use a clear action name like <code>view products</code> or <code>export reports</code>.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Group</label>
                    <input type="text" name="group" value="{{ old('group', $permission->group) }}" class="form-control" list="group-list"
                           placeholder="e.g. Reports">
                    <datalist id="group-list">
                        @foreach ($groups as $g)<option value="{{ $g }}">@endforeach
                    </datalist>
                    <div class="form-text">Groups organise permissions on the role screen. Pick an existing one or type a new group.</div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save</button>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
