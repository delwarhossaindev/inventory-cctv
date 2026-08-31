@extends('layouts.app')
@section('title', 'Add Product')
@section('heading', 'Add Product')

@section('content')
<form action="{{ route('admin.products.store') }}" method="POST">
    @include('admin.products._form')
</form>
@endsection
