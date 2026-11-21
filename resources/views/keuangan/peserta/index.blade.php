@extends('keuangan.layouts.app')

@section('title', 'Manajemen Peserta')

@section('content')
    @include('superadmin.peserta.index', ['routePrefix' => 'keuangan'])
@endsection
