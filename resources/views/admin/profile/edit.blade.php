@extends('layouts.app')
@section('title', 'My Profile')
@section('heading', 'My Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px">
                        <i class="bi bi-person-fill fs-1 text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-0">{{ $user->name }}</h5>
                    <div class="text-muted small">{{ $user->email }}</div>
                    @if ($user->roles->count())
                        <span class="badge bg-primary mt-1">{{ $user->roles->first()->name }}</span>
                    @endif
                </div>

                <form action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.profile.password') }}" class="btn btn-outline-warning"><i class="bi bi-key me-1"></i>Change Password</a>
                        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
