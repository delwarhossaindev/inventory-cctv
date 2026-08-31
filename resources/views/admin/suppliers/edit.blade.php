@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('heading', 'Edit Supplier')

@section('content')
<form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST">
    @method('PUT')
    @include('admin.suppliers._form')
</form>
@endsection
