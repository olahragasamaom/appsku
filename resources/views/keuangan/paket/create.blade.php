@extends('keuangan.layouts.app')

@section('title', 'Tambah Paket')

@section('content')
    @include('superadmin.paket.create', ['routePrefix' => 'keuangan'])
@endsection
