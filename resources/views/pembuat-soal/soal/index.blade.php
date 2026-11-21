@extends('pembuat-soal.layouts.app')

@section('title', 'Bank Soal')

@section('content')
    @php
        // Override route name generator untuk pembuat-soal
        $routePrefix = 'pembuat-soal';
    @endphp
    
    @include('superadmin.soal.index', ['routePrefix' => 'pembuat-soal'])
@endsection
