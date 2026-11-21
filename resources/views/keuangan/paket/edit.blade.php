@extends('keuangan.layouts.app')

@section('title', 'Edit Paket')

@section('content')
    @include('superadmin.paket.edit', ['routePrefix' => 'keuangan'])
@endsection
