@extends('layouts.app')
@section('title', 'Change Password')
@section('heading', 'Change Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:64px;height:64px">
                        <i class="bi bi-key-fill fs-2 text-warning"></i>
                    </div>
                    <h6 class="fw-bold">Change Your Password</h6>
                </div>

                <form action="{{ route('admin.profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.profile.edit') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Profile</a>
                        <button class="btn btn-warning"><i class="bi bi-check-lg me-1"></i>Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
