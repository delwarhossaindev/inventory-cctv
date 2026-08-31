@extends('layouts.app')
@section('title', 'Add Unit')
@section('heading', 'Add Unit')

@section('content')
<form action="{{ route('admin.units.store') }}" method="POST">
    @include('admin.units._form')
</form>
@endsection
