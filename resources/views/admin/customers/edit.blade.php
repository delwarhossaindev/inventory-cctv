@extends('layouts.app')
@section('title', 'Edit Customer')
@section('heading', 'Edit Customer')

@section('content')
<form action="{{ route('admin.customers.update', $customer) }}" method="POST">
    @method('PUT')
    @include('admin.customers._form')
</form>
@endsection
