@extends('superadmin.layouts.app')

@section('title', 'Monitoring Pengawas')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Monitoring Pengawas</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Monitoring Pengawas</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('superadmin.ujian.peserta-offline.kehadiran.index', $ujian) }}" class="btn btn-secondary">
                Kelola Kehadiran
            </a>
            <a href="{{ route('superadmin.ujian.index') }}" class="btn btn-ghost">Kembali</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto space-y-6" x-data="monitoringDashboard()" x-init="init()">
        @if(session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-sm text-secondary-600">Update terakhir:</span>
                <span class="text-sm font-medium text-secondary-800" x-text="lastUpdated"></span>
                <span class="inline-flex items-center gap-1.5 text-xs text-success-600">
                    <span class="w-2 h-2 rounded-full bg-success-500 animate-pulse"></span>
                    Auto refresh 5 detik
                </span>
            </div>
            <button type="button" @click="fetchLive()" class="btn btn-ghost btn-sm">
                Refresh Sekarang
            </button>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-xs text-secondary-500 uppercase font-medium">Total Peserta</div>
                    <div class="text-2xl font-bold text-secondary-800 mt-1" x-text="stats.total_peserta">-</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="text-xs text-secondary-500 uppercase font-medium">Ditandai Hadir</div>
                    <div class="text-2xl font-bold text-success-600 mt-1" x-text="stats.total_hadir">-</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="text-xs text-secondary-500 uppercase font-medium">Login</div>
                    <div class="text-2xl font-bold text-primary-600 mt-1" x-text="stats.logged_in">-</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="text-xs text-secondary-500 uppercase font-medium">Sedang Ujian</div>
                    <div class="text-2xl font-bold text-warning-600 mt-1" x-text="stats.sedang_ujian">-</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="text-xs text-secondary-500 uppercase font-medium">Selesai</div>
                    <div class="text-2xl font-bold text-secondary-800 mt-1" x-text="stats.selesai">-</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Peserta</h3>
                <p class="text-xs text-secondary-500 mt-1">
                    <span class="inline-flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-success-500"></span> Online</span>
                    <span class="inline-flex items-center gap-1 ml-3"><span class="w-2 h-2 rounded-full bg-warning-500"></span> Idle (>30 detik)</span>
                    <span class="inline-flex items-center gap-1 ml-3"><span class="w-2 h-2 rounded-full bg-secondary-400"></span> Offline</span>
                </p>
            </div>
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-secondary-200 bg-secondary-50">
                                <th class="text-left py-3 px-4 text-xs font-medium text-secondary-700 uppercase">No</th>
                                <th class="text-left py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Nomor Peserta</th>
                                <th class="text-left py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Nama Peserta</th>
                                <th class="text-center py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Kehadiran</th>
                                <th class="text-center py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Status Ujian</th>
                                <th class="text-left py-3 px-4 text-xs font-medium text-secondary-700 uppercase">IP Address</th>
                                <th class="text-left py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Last Activity</th>
                                <th class="text-center py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="participants.length === 0">
                                <tr>
                                    <td colspan="8" class="text-center py-8 text-secondary-500">
                                        Belum ada peserta.
                                    </td>
                                </tr>
                            </template>
                            <template x-for="(p, index) in participants" :key="p.peserta_offline_id">
                                <tr class="border-b border-secondary-100 hover:bg-secondary-50">
                                    <td class="py-3 px-4 text-sm text-secondary-600" x-text="index + 1"></td>
                                    <td class="py-3 px-4 text-sm font-medium text-secondary-800" x-text="p.nomor_peserta"></td>
                                    <td class="py-3 px-4 text-sm text-secondary-700" x-text="p.nama_peserta"></td>
                                    <td class="py-3 px-4 text-center">
                                        <template x-if="p.status_kehadiran === 'hadir'">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-700">
                                                Hadir
                                            </span>
                                        </template>
                                        <template x-if="p.status_kehadiran !== 'hadir'">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-secondary-100 text-secondary-600">
                                                Belum Hadir
                                            </span>
                                        </template>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full"
                                                  :class="{
                                                    'bg-success-500': p.is_online,
                                                    'bg-warning-500': !p.is_online && (p.status_ujian === 'sedang_ujian_idle' || p.status_ujian === 'logged_in_idle'),
                                                    'bg-secondary-400': p.status_ujian === 'offline'
                                                  }"></span>
                                            <span class="text-xs text-secondary-700" x-text="formatStatus(p.status_ujian)"></span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-secondary-500 font-mono" x-text="p.ip_address || '-'"></td>
                                    <td class="py-3 px-4 text-xs text-secondary-500" x-text="formatTime(p.last_activity)"></td>
                                    <td class="py-3 px-4 text-center">
                                        <template x-if="p.session_id">
                                            <form :action="`{{ url('/superadmin/ujian/' . $ujian->id . '/pengawas/force-logout') }}/${p.session_id}`" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        onclick="return confirm('Yakin ingin force logout peserta ini?')"
                                                        class="btn btn-ghost btn-sm text-danger-600">
                                                    Force Logout
                                                </button>
                                            </form>
                                        </template>
                                        <template x-if="!p.session_id">
                                            <span class="text-xs text-secondary-400">-</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function monitoringDashboard() {
            return {
                stats: {
                    total_peserta: 0,
                    total_hadir: 0,
                    logged_in: 0,
                    sedang_ujian: 0,
                    selesai: 0,
                },
                participants: [],
                lastUpdated: '-',
                refreshInterval: null,

                init() {
                    this.fetchLive();
                    this.refreshInterval = setInterval(() => this.fetchLive(), 5000);
                },

                async fetchLive() {
                    try {
                        const response = await fetch('{{ route('superadmin.ujian.pengawas.live', $ujian) }}');
                        const data = await response.json();
                        this.stats = data.stats;
                        this.participants = data.participants;
                        this.lastUpdated = new Date().toLocaleTimeString('id-ID');
                    } catch (error) {
                        console.error('Failed to fetch live data:', error);
                    }
                },

                formatStatus(status) {
                    const map = {
                        'offline': 'Belum Login',
                        'logged_in_online': 'Login (Online)',
                        'logged_in_idle': 'Login (Idle)',
                        'sedang_ujian_online': 'Sedang Ujian',
                        'sedang_ujian_idle': 'Ujian (Idle)',
                    };
                    return map[status] || status;
                },

                formatTime(iso) {
                    if (!iso) return '-';
                    const date = new Date(iso);
                    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                }
            };
        }
    </script>
@endsection
