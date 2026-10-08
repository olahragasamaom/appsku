@extends('superadmin.layouts.app')

@section('title', 'Analisis Soal - '.$ujian->nama_ujian)

@section('breadcrumb')
    <a href="{{ route('superadmin.hasil-ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Hasil Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">{{ $ujian->nama_ujian }}</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Analisis Soal</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Analisis Soal</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }} - {{ $totalPeserta }} peserta telah selesai</p>
        </div>
        <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="btn btn-ghost">Kembali</a>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="card">
            <div class="card-body-sm">
                <x-table>
                    <x-slot name="header">
                        <th class="px-4 py-3 text-left w-16">No</th>
                        <th class="px-4 py-3 text-left">Soal</th>
                        <th class="px-4 py-3 text-center w-32">Kunci</th>
                        <th class="px-4 py-3 text-center w-24">Benar</th>
                        <th class="px-4 py-3 text-center w-24">Salah</th>
                        <th class="px-4 py-3 text-center w-32">Belum Jawab</th>
                        <th class="px-4 py-3 text-center w-40">% Benar</th>
                    </x-slot>
                    @foreach($statistics as $index => $stat)
                        @php
                            $isSistemBenar = ($stat['soal']?->subIndikator?->subJenisUjian?->sistem_penilaian ?? 'benar_salah') === 'benar_salah';
                            $persenColor = $stat['persen_benar'] >= 75 ? 'success' : ($stat['persen_benar'] >= 50 ? 'warning' : 'danger');
                        @endphp
                        <tr class="hover:bg-secondary-50 border-b border-secondary-100">
                            <td class="px-4 py-4 font-semibold text-secondary-700">{{ $loop->iteration }}</td>
                            <td class="px-4 py-4">
                                <p class="text-sm text-secondary-800 line-clamp-2">{!! strip_tags($stat['soal']?->soal ?? '-') !!}</p>
                                <span class="text-xs text-secondary-400">{{ $stat['soal']?->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian ?? '' }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($isSistemBenar && $stat['soal']?->kunci_jawaban)
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-success-100 text-success-700 font-bold">{{ $stat['soal']->kunci_jawaban }}</span>
                                @else
                                    <span class="text-xs text-secondary-400">Bobot</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($isSistemBenar)
                                    <span class="text-success-600 font-semibold">{{ $stat['total_benar'] }}</span>
                                @else
                                    <span class="text-secondary-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($isSistemBenar)
                                    <span class="text-danger-600 font-semibold">{{ $stat['total_salah'] }}</span>
                                @else
                                    <span class="text-secondary-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-secondary-500">{{ $stat['belum_jawab'] }}</span>
                            </td>
                            <td class="px-4 py-4">
                                @if($isSistemBenar && $stat['total_jawab'] > 0)
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-secondary-100 rounded-full h-2 overflow-hidden">
                                            <div class="bg-{{ $persenColor }}-500 h-2 rounded-full transition-all" style="width: {{ $stat['persen_benar'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-{{ $persenColor }}-700 w-10 text-right">{{ $stat['persen_benar'] }}%</span>
                                    </div>
                                @else
                                    <span class="text-xs text-secondary-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </div>
        </div>
    </div>
@endsection
