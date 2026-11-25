@extends('pembuat-soal.layouts.app')

@section('title', 'Edit Ujian')

@section('content')
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900">Edit Ujian</h1>
                <p class="text-secondary-500 text-sm mt-1">Ubah konfigurasi dan parameter ujian</p>
            </div>
            <a href="{{ route('pembuat-soal.ujian.index') }}" class="btn btn-secondary">
                &larr; Kembali
            </a>
        </div>

        <form action="{{ route('pembuat-soal.ujian.update', $ujian) }}" method="POST">
            @csrf
            @method('PUT')
            @include('superadmin.ujian._form', [
                'submitLabel' => 'Simpan Perubahan',
                'cancelUrl' => route('pembuat-soal.ujian.index')
            ])
        </form>
    </div>
@endsection
