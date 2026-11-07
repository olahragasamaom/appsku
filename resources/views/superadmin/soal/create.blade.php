@extends('superadmin.layouts.app')

@section('title', 'Tambah Soal')

@section('breadcrumb')
    @if($ujian)
        <a href="{{ route('superadmin.ujian.soal.index', ['ujian' => $ujian, 'jenis_ujian_id' => request('jenis_ujian_id')]) }}" class="text-secondary-500 hover:text-secondary-700">{{ $ujian->nama_ujian }}</a>
        <span class="mx-2 text-secondary-400">/</span>
    @else
        <a href="{{ route('superadmin.soal.index') }}" class="text-secondary-500 hover:text-secondary-700">Bank Soal</a>
        <span class="mx-2 text-secondary-400">/</span>
    @endif
    <span class="text-secondary-900 font-medium">Tambah Soal</span>
@endsection

@section('header')
    <h1 class="text-2xl font-bold text-secondary-900">Tambah Soal</h1>
@endsection

@section('content')
    <form action="{{ route('superadmin.soal.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('superadmin.soal._form', [
            'submitLabel' => 'Simpan Soal',
            'locked' => isset($subIndikator) && $subIndikator !== null,
            'subIndikator' => $subIndikator ?? null,
        ])
    </form>
@endsection
