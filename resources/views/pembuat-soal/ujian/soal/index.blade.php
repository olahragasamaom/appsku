@extends('pembuat-soal.layouts.app')

@section('title', 'Kelola Soal Ujian')

@section('content')
    @php
        // Override route prefix untuk pembuat-soal
        $routePrefix = 'pembuat-soal';
    @endphp
    
    @include('superadmin.ujian.soal.index', ['routePrefix' => 'pembuat-soal'])
@endsection
