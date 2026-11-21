@extends('pembuat-soal.layouts.app')

@section('title', 'Tambah Soal ke Ujian')

@section('content')
<div class="p-8">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Tambah Soal ke Ujian</h1>
    
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('pembuat-soal.ujian.soal.store', $ujian) }}" method="POST" class="space-y-6">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Soal dari Bank Soal</label>
                <select name="soal_id" id="soal_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">-- Pilih Soal --</option>
                    @foreach($soalOptions as $soal)
                        <option value="{{ $soal->id }}">{{ Str::limit(strip_tags($soal->soal), 80) }}</option>
                    @endforeach
                </select>
                @error('soal_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-2">Jenis Ujian</label>
                <select name="jenis_ujian_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">-- Pilih Jenis Ujian --</option>
                    @foreach($ujian->jenisUjians as $jenis)
                        <option value="{{ $jenis->id }}">{{ $jenis->nama_jenis_ujian }}</option>
                    @endforeach
                </select>
                @error('jenis_ujian_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                    Tambah Soal
                </button>
                <a href="{{ route('pembuat-soal.ujian.soal.index', $ujian) }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
