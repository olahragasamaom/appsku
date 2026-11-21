@extends('keuangan.layouts.app')

@section('title', 'Verifikasi Pembayaran')

@section('content')
    @include('superadmin.pembayaran.index', ['routePrefix' => 'keuangan'])
@endsection
