@extends('layouts.app')
@section('title', 'Edit Role')
@section('heading', 'Edit Role')

@section('content')
<form action="{{ route('admin.roles.update', $role) }}" method="POST">
    @method('PUT')
    @include('admin.roles._form')
</form>
@endsection
