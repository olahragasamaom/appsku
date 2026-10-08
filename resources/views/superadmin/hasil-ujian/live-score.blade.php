@extends('superadmin.layouts.app')

@section('title', 'Live Score - '.$ujian->nama_ujian)

@section('breadcrumb')
    <a href="{{ route('superadmin.hasil-ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Hasil Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">{{ $ujian->nama_ujian }}</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Live Score</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Live Score</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }} - Update otomatis setiap 5 detik</p>
        </div>
        <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="btn btn-ghost">Kembali</a>
    </div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto" x-data="liveScoring({ url: '{{ route('superadmin.ujian.monitoring.live-data', $ujian) }}' })" x-init="start()">
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="text-sm text-secondary-500">Terakhir diperbarui: <span x-text="updatedAt || '-'"></span></p>
            <p class="text-sm text-secondary-600 mt-1" x-show="passingGrade">Passing Grade: <span class="font-semibold" x-text="passingGrade"></span></p>
        </div>
        <button type="button" @click="load()" class="btn btn-ghost btn-sm">Refresh</button>
    </div>

    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left w-16">Rank</th>
                    <th class="px-6 py-3 text-left">Nama</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Nilai</th>
                    <th class="px-6 py-3 text-center">Kelulusan</th>
                </x-slot>
                <template x-for="row in peserta" :key="row.id">
                    <tr class="hover:bg-secondary-50" :class="row.is_pass ? 'bg-success-50' : ''">
                        <td class="px-6 py-4 font-semibold text-secondary-700" x-text="row.rank"></td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-secondary-900" x-text="row.nama"></span>
                            <span class="block text-xs text-secondary-400" x-text="row.username"></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                  :class="row.status === 'selesai' ? 'bg-success-100 text-success-700' : 'bg-warning-100 text-warning-700'"
                                  x-text="row.status"></span>
                        </td>
                        <td class="px-6 py-4 text-center font-semibold" 
                            :class="row.total_nilai !== null ? (row.is_pass ? 'text-success-600' : 'text-danger-600') : 'text-secondary-500'">
                            <span x-text="row.total_nilai !== null ? row.total_nilai.toFixed(2) : '-'"></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <template x-if="row.lulus === true"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-700">✓ Lulus</span></template>
                            <template x-if="row.lulus === false"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-danger-100 text-danger-700">✕ Tidak Lulus</span></template>
                            <template x-if="row.lulus === null">
                                <template x-if="row.is_pass"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-700">✓ Lulus*</span></template>
                                <template x-if="!row.is_pass"><span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-danger-100 text-danger-700">✕ Belum Lulus*</span></template>
                            </template>
                        </td>
                    </tr>
                </template>
                <template x-if="peserta.length === 0">
                    <tr><td colspan="5" class="px-6 py-12 text-center text-secondary-500">Belum ada data peserta</td></tr>
                </template>
            </x-table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        function liveScoring({ url }) {
            return {
                url,
                peserta: [],
                updatedAt: null,
                passingGrade: 0,
                interval: null,
                start() {
                    this.load();
                    this.interval = setInterval(() => this.load(), 5000);
                },
                async load() {
                    try {
                        const res = await fetch(this.url);
                        const data = await res.json();
                        this.peserta = data.peserta || [];
                        this.updatedAt = data.updated_at;
                        this.passingGrade = data.passing_grade || 0;
                    } catch (e) {
                        console.error('Load live data error', e);
                    }
                },
            };
        }
    </script>
@endpush
