@extends('keuangan.layouts.app')

@section('title', 'Tambah Peserta')

@section('content')
    @include('superadmin.peserta.create', ['routePrefix' => 'keuangan'])
@endsection
