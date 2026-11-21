@extends('keuangan.layouts.app')

@section('title', 'Detail Pembayaran')

@section('content')
    @include('superadmin.pembayaran.show', ['routePrefix' => 'keuangan'])
@endsection
