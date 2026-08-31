@extends('layouts.app')
@section('title', 'Add User')
@section('heading', 'Add User')

@section('content')
<form action="{{ route('admin.users.store') }}" method="POST">
    @include('admin.users._form')
</form>
@endsection
