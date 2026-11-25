@extends('pembuat-soal.layouts.app')

@section('title', 'Tambah Ujian')

@section('content')
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900">Tambah Ujian</h1>
                <p class="text-secondary-500 text-sm mt-1">Buat ujian baru untuk peserta</p>
            </div>
            <a href="{{ route('pembuat-soal.ujian.index') }}" class="btn btn-secondary">
                &larr; Kembali
            </a>
        </div>

        <form action="{{ route('pembuat-soal.ujian.store') }}" method="POST">
            @csrf
            @include('superadmin.ujian._form', [
                'submitLabel' => 'Simpan Ujian',
                'cancelUrl' => route('pembuat-soal.ujian.index')
            ])
        </form>
    </div>
@endsection
