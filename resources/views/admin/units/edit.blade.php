@extends('layouts.app')
@section('title', 'Edit Unit')
@section('heading', 'Edit Unit')

@section('content')
<form action="{{ route('admin.units.update', $unit) }}" method="POST">
    @method('PUT')
    @include('admin.units._form')
</form>
@endsection
