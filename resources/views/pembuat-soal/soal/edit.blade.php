@extends('pembuat-soal.layouts.app')

@section('title', 'Edit Soal')

@section('content')
    @php
        $routePrefix = 'pembuat-soal';
    @endphp
    @include('superadmin.soal.edit', ['routePrefix' => 'pembuat-soal'])
@endsection
