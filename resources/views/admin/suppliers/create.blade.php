@extends('layouts.app')
@section('title', 'Add Supplier')
@section('heading', 'Add Supplier')

@section('content')
<form action="{{ route('admin.suppliers.store') }}" method="POST">
    @include('admin.suppliers._form')
</form>
@endsection
