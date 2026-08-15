@extends('peserta.layouts.app')

@section('title', 'Hasil Ujian')

@section('content')
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('peserta.dashboard') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Kembali ke Dashboard</a>

        <div class="card mt-4">
            <div class="card-body text-center">
                <h1 class="text-xl font-bold text-slate-800">{{ $ujian->nama_ujian }}</h1>

                @if($ujian->tampilkan_hasil)
                    <p class="text-4xl font-extrabold text-primary-600 mt-4">{{ $peserta->total_nilai ?? 0 }}</p>
                    <p class="text-slate-500 text-sm">Total Nilai</p>

                    <div class="mt-4">
                        @if($peserta->lulus === true)
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-success-100 text-success-700">LULUS</span>
                        @elseif($peserta->lulus === false)
                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold bg-danger-100 text-danger-700">TIDAK LULUS</span>
                        @endif
                    </div>
                @else
                    <p class="mt-6 text-slate-600">Ujian telah selesai. Hasil belum ditampilkan oleh penyelenggara.</p>
                @endif
            </div>
        </div>

        @if($ujian->tampilkan_hasil)
            <div class="card mt-6">
                <div class="card-body-sm">
                    <x-table>
                        <x-slot name="header">
                            <th class="px-6 py-3 text-left">Jenis Ujian</th>
                            <th class="px-6 py-3 text-center">Nilai</th>
                            <th class="px-6 py-3 text-center">Passing Grade</th>
                            <th class="px-6 py-3 text-center">Status</th>
                        </x-slot>
                        @foreach($breakdown as $row)
                            <tr>
                                <td class="px-6 py-4 font-medium text-slate-800">{{ $row['nama'] }}</td>
                                <td class="px-6 py-4 text-center">{{ $row['nilai'] }}</td>
                                <td class="px-6 py-4 text-center">{{ $row['passing_grade'] ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if($row['lulus'] === true)
                                        <span class="text-success-600 text-sm font-medium">Lulus</span>
                                    @elseif($row['lulus'] === false)
                                        <span class="text-danger-600 text-sm font-medium">Tidak Lulus</span>
                                    @else
                                        <span class="text-slate-400 text-sm">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('peserta.ujian.leaderboard', $ujian) }}" class="btn btn-secondary w-full sm:w-auto">
                    Lihat Peringkat
                </a>
                <a href="{{ route('peserta.ujian.pembahasan', $ujian) }}" class="btn btn-primary w-full sm:w-auto">
                    Lihat Pembahasan Jawaban
                </a>
            </div>
        @endif
    </div>
@endsection
