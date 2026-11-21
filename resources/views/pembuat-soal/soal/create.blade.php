@extends('pembuat-soal.layouts.app')

@section('title', 'Buat Soal Baru')

@section('content')
    @php
        $routePrefix = 'pembuat-soal';
    @endphp
    @include('superadmin.soal.create', ['routePrefix' => 'pembuat-soal'])
@endsection
