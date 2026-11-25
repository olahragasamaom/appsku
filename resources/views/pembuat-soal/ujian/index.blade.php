@extends('pembuat-soal.layouts.app')

@section('title', 'Manajemen Ujian')

@section('content')
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900">Manajemen Ujian</h1>
                <p class="text-secondary-500 mt-1">Buat dan atur jadwal serta konfigurasi ujian</p>
            </div>
            <a href="{{ route('pembuat-soal.ujian.create') }}" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Ujian
            </a>
        </div>

        @include('superadmin.ujian._table')
    </div>
@endsection