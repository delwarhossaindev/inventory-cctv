@extends('layouts.app')
@section('title', 'Add Customer')
@section('heading', 'Add Customer')

@section('content')
<form action="{{ route('admin.customers.store') }}" method="POST">
    @include('admin.customers._form')
</form>
@endsection
