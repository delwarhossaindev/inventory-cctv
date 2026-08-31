@extends('layouts.app')
@section('title', 'Edit Permission')
@section('heading', 'Edit Permission')

@section('content')
<form action="{{ route('admin.permissions.update', $permission) }}" method="POST">
    @method('PUT')
    @include('admin.permissions._form')
</form>
@endsection
