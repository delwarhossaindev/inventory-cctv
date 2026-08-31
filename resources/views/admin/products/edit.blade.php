@extends('layouts.app')
@section('title', 'Edit Product')
@section('heading', 'Edit Product')

@section('content')
<form action="{{ route('admin.products.update', $product) }}" method="POST">
    @method('PUT')
    @include('admin.products._form')
</form>
@endsection
