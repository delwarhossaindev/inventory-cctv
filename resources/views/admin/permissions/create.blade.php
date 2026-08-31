@extends('layouts.app')
@section('title', 'Add Permission')
@section('heading', 'Add Permission')

@section('content')
<form action="{{ route('admin.permissions.store') }}" method="POST">
    @include('admin.permissions._form')
</form>
@endsection
