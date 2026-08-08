@extends('superadmin.layouts.app')

@section('title', 'Simulasi Soal')

@section('breadcrumb')
    <a href="{{ route('superadmin.soal.index') }}" class="text-secondary-500 hover:text-secondary-700">Bank Soal</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Simulasi Soal</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Simulasi Tampilan Soal</h1>
            <p class="text-secondary-500 mt-1">Beginilah soal ini akan terlihat oleh peserta saat ujian berlangsung.</p>
        </div>
        <button type="button" onclick="window.close()" class="btn btn-secondary">Tutup Tab</button>
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto mt-6">
        <div class="card shadow-lg border-primary-100">
            <div class="card-body p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <span class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-lg">1</span>
                    <div class="flex-1 min-w-0">
                        {{-- Identitas Soal --}}
                        <div class="mb-4 pb-4 border-b border-slate-100">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 mb-2">
                                {{ $soal->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? 'Umum' }} &mdash; {{ $soal->subIndikator?->nama_sub_indikator ?? 'Tanpa Indikator' }}
                            </span>
                        </div>

                        {{-- Teks Soal & Gambar --}}
                        <div class="prose prose-slate max-w-none text-slate-800 text-base leading-relaxed">
                            {!! $soal->soal !!}
                        </div>
                        @if($soal->gambar_soal)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($soal->gambar_soal) }}" alt="Gambar soal" class="mt-4 max-h-80 rounded-xl border border-slate-200 shadow-sm">
                        @endif

                        {{-- Opsi Jawaban --}}
                        <div class="mt-8 space-y-3">
                            @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
                                @php 
                                    $opsiText = $soal->{'opsi_'.strtolower($opsi)}; 
                                    $gambarOpsi = $soal->{'gambar_opsi_'.strtolower($opsi)};
                                @endphp
                                
                                @if($opsiText !== null && $opsiText !== '')
                                    <label class="flex items-start gap-4 p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors group">
                                        <div class="flex items-center h-6">
                                            <input type="radio" name="simulasi_opsi" value="{{ $opsi }}" class="w-5 h-5 text-primary-600 border-slate-300 focus:ring-primary-600">
                                        </div>
                                        <div class="flex-1">
                                            <span class="text-base text-slate-700 group-hover:text-slate-900 font-medium mr-2">{{ $opsi }}.</span>
                                            <span class="text-base text-slate-700 group-hover:text-slate-900">{{ $opsiText }}</span>
                                            
                                            @if($gambarOpsi)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($gambarOpsi) }}" alt="Opsi {{ $opsi }}" class="mt-3 max-h-40 rounded-lg border border-slate-200">
                                            @endif
                                        </div>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-footer bg-slate-50 border-t border-slate-100 flex justify-between items-center rounded-b-xl px-6 py-4">
                <p class="text-sm text-slate-500">
                    Sistem Penilaian: <span class="font-medium text-slate-700">{{ $soal->subIndikator?->subJenisUjian?->sistem_penilaian === 'benar_salah' ? 'Benar-Salah' : 'Poin per Jawaban' }}</span>
                </p>
                <a href="{{ route('superadmin.soal.edit', $soal) }}" class="btn btn-primary">Edit Soal Ini</a>
            </div>
        </div>
    </div>
@endsection