@extends('layouts.app')
@section('title', 'Add Category')
@section('heading', 'Add Category')

@section('content')
<form action="{{ route('admin.categories.store') }}" method="POST">
    @include('admin.categories._form')
</form>
@endsection
