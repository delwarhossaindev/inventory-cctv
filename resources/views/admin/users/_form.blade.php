@csrf
@php $userRoles = old('roles', $user->roles->pluck('name')->all()); @endphp
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required autofocus>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password @if(!$user->exists)<span class="text-danger">*</span>@endif</label>
                        <input type="password" name="password" class="form-control" @if(!$user->exists) required @endif>
                        @if($user->exists)<div class="form-text">Leave blank to keep current password.</div>@endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" @if(!$user->exists) required @endif>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Roles</label>
                        <div class="row g-2">
                            @foreach ($roles as $role)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" id="role-{{ $role->id }}"
                                               class="form-check-input" @checked(in_array($role->name, $userRoles))>
                                        <label for="role-{{ $role->id }}" class="form-check-label">{{ $role->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
