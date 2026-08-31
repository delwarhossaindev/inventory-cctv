@extends('layouts.app')
@section('title', 'Edit User')
@section('heading', 'Edit User')

@section('content')
<form action="{{ route('admin.users.update', $user) }}" method="POST">
    @method('PUT')
    @include('admin.users._form')
</form>
@endsection
