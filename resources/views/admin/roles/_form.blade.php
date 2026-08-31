@csrf
@php $assigned = old('permissions', $assigned); $isSuper = $role->name === 'Super Admin'; @endphp
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="col-md-6">
                    <label class="form-label">Role Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" class="form-control" required autofocus @disabled($isSuper)>
                    @if($isSuper)<div class="form-text">The Super Admin role name is locked (it grants full access).</div>@endif
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Permissions</span>
                @unless($isSuper)
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="check-all">Select all</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="uncheck-all">Clear</button>
                    </div>
                @endunless
            </div>
            <div class="card-body">
                @if($isSuper)
                    <div class="alert alert-info mb-0">Super Admin automatically has <strong>every</strong> permission, including future ones. Nothing to configure here.</div>
                @else
                    <div class="row g-3">
                        @foreach ($groups as $group => $permissions)
                            <div class="col-md-6 col-lg-4">
                                <div class="border rounded h-100">
                                    <div class="bg-light px-3 py-2 fw-semibold small d-flex justify-content-between">
                                        <span>{{ $group }}</span>
                                        <input type="checkbox" class="form-check-input group-toggle">
                                    </div>
                                    <div class="p-3">
                                        @foreach ($permissions as $perm)
                                            <div class="form-check">
                                                <input type="checkbox" name="permissions[]" value="{{ $perm }}" id="p-{{ $loop->parent->index }}-{{ $loop->index }}"
                                                       class="form-check-input perm-check" @checked(in_array($perm, $assigned))>
                                                <label for="p-{{ $loop->parent->index }}-{{ $loop->index }}" class="form-check-label text-capitalize">{{ $perm }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save Role</button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const all = document.querySelectorAll('.perm-check');
    document.getElementById('check-all')?.addEventListener('click', () => all.forEach(c => c.checked = true));
    document.getElementById('uncheck-all')?.addEventListener('click', () => all.forEach(c => c.checked = false));

    // Per-group toggle
    document.querySelectorAll('.group-toggle').forEach(function (toggle) {
        const box = toggle.closest('.border');
        const checks = box.querySelectorAll('.perm-check');
        const sync = () => { toggle.checked = [...checks].every(c => c.checked); };
        toggle.addEventListener('change', () => checks.forEach(c => c.checked = toggle.checked));
        checks.forEach(c => c.addEventListener('change', sync));
        sync();
    });
});
</script>
@endpush
