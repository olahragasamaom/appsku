@extends('keuangan.layouts.app')

@section('title', 'Edit Peserta')

@section('content')
    @include('superadmin.peserta.edit', ['routePrefix' => 'keuangan'])
@endsection
