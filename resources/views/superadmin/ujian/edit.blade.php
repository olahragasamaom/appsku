@extends('superadmin.layouts.app')

@section('title', 'Edit Ujian')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Edit Ujian</span>
@endsection

@section('header')
    <h1 class="text-2xl font-bold text-secondary-900">Edit Ujian</h1>
@endsection

@section('content')
    <form action="{{ route('superadmin.ujian.update', $ujian) }}" method="POST">
        @csrf
        @method('PUT')
        @include('superadmin.ujian._form', ['submitLabel' => 'Simpan Perubahan'])
    </form>
@endsection
