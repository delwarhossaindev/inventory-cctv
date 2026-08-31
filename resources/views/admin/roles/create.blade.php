@extends('layouts.app')
@section('title', 'Add Role')
@section('heading', 'Add Role')

@section('content')
<form action="{{ route('admin.roles.store') }}" method="POST">
    @include('admin.roles._form')
</form>
@endsection
