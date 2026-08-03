@extends('superadmin.layouts.app')

@section('title', 'Edit Peserta')

@section('breadcrumb')
    <a href="{{ route('superadmin.peserta.index') }}" class="text-secondary-500 hover:text-secondary-700">Master Peserta</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Edit Peserta</span>
@endsection

@section('header')
    <h1 class="text-2xl font-bold text-secondary-900">Edit Peserta</h1>
@endsection

@section('content')
    <form action="{{ route('superadmin.peserta.update', $peserta) }}" method="POST">
        @csrf
        @method('PUT')
        @include('superadmin.peserta._form', ['submitLabel' => 'Simpan Perubahan'])
    </form>
@endsection
