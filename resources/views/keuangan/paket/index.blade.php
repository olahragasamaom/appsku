@extends('keuangan.layouts.app')

@section('title', 'Manajemen Paket')

@section('content')
    @include('superadmin.paket.index', ['routePrefix' => 'keuangan'])
@endsection
