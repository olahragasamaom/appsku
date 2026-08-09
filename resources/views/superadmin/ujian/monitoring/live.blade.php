@extends('superadmin.layouts.app')

@section('title', 'Live Scoring')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Live Scoring</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Live Scoring — {{ $ujian->nama_ujian }}</h1>
            <p class="text-secondary-500 mt-1">Peringkat berjalan diperbarui otomatis</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('superadmin.ujian.peserta.index', $ujian) }}" class="btn btn-secondary btn-sm">Kelola Peserta</a>
            <a href="{{ route('superadmin.ujian.monitoring.ranking', $ujian) }}" class="btn btn-secondary btn-sm">Perankingan Final</a>
        </div>
    </div>
@endsection

@section('content')
<div x-data="liveScoring({ url: '{{ route('superadmin.ujian.monitoring.live-data', $ujian) }}' })" x-init="start()">
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-secondary-500">Terakhir diperbarui: <span x-text="updatedAt || '-'"></span></p>
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
                <template x-for="row in peserta" :key="row.username">
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4 font-semibold text-secondary-700" x-text="row.rank"></td>
                        <td class="px-6 py-4">
                            <span class="font-medium text-secondary-900" x-text="row.nama"></span>
                            <span class="block text-xs text-secondary-400" x-text="row.username"></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-600" x-text="row.status"></span>
                        </td>
                        <td class="px-6 py-4 text-center" x-text="row.total_nilai !== null ? row.total_nilai : '-'"></td>
                        <td class="px-6 py-4 text-center">
                            <template x-if="row.lulus === true"><span class="text-success-600 text-sm font-medium">Lulus</span></template>
                            <template x-if="row.lulus === false"><span class="text-danger-600 text-sm font-medium">Tidak Lulus</span></template>
                            <template x-if="row.lulus === null"><span class="text-secondary-400 text-sm">-</span></template>
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
    document.addEventListener('alpine:init', () => {
        Alpine.data('liveScoring', (config) => ({
            url: config.url,
            peserta: [],
            updatedAt: null,
            interval: null,
            start() {
                this.load();
                this.interval = setInterval(() => this.load(), 5000);
            },
            async load() {
                try {
                    const res = await fetch(this.url, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    this.peserta = data.peserta;
                    this.updatedAt = data.updated_at;
                } catch (e) {
                    console.error('Gagal memuat live scoring:', e);
                }
            }
        }));
    });
</script>
@endpush
