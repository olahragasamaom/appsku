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
                                        <div class="inline-flex flex-col gap-1">
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
                                            <template x-if="p.is_blocked">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-danger-100 text-danger-700" :title="p.blocked_reason">
                                                    ⛔ Diblokir
                                                </span>
                                            </template>
                                        </div>
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
                                        <div class="inline-flex flex-wrap gap-1 justify-center">
                                            <template x-if="p.session_id">
                                                <form :action="`{{ url('/superadmin/ujian/' . $ujian->id . '/pengawas/force-logout') }}/${p.session_id}`" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            onclick="return confirm('Yakin ingin force logout peserta ini?')"
                                                            class="btn btn-ghost btn-sm text-warning-600"
                                                            title="Force Logout">
                                                        Logout
                                                    </button>
                                                </form>
                                            </template>

                                            <template x-if="p.status_ujian === 'sedang_ujian_online' || p.status_ujian === 'sedang_ujian_idle'">
                                                <button type="button"
                                                        @click="openExtendModal(p)"
                                                        class="btn btn-ghost btn-sm text-primary-600"
                                                        title="Tambah Waktu">
                                                    +Waktu
                                                </button>
                                            </template>

                                            <template x-if="!p.is_blocked">
                                                <button type="button"
                                                        @click="openBlockModal(p)"
                                                        class="btn btn-ghost btn-sm text-danger-600"
                                                        title="Blokir Peserta">
                                                    Blokir
                                                </button>
                                            </template>

                                            <template x-if="p.is_blocked">
                                                <form :action="`{{ url('/superadmin/ujian/' . $ujian->id . '/pengawas/peserta') }}/${p.peserta_offline_id}/unblock`" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                            onclick="return confirm('Yakin ingin unblock peserta ini?')"
                                                            class="btn btn-ghost btn-sm text-success-600"
                                                            title="Unblock Peserta">
                                                        Unblock
                                                    </button>
                                                </form>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

        <!-- Modal: Blokir Peserta -->
        <div x-show="showBlockModal" x-cloak
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 99999;">
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5);"
                 @click="closeBlockModal()"></div>
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; padding: 1rem; pointer-events: none;">
                <div style="pointer-events: auto; width: 100%; max-width: 28rem; background-color: white; border-radius: 1rem;">
                    <form :action="`{{ url('/superadmin/ujian/' . $ujian->id . '/pengawas/peserta') }}/${selectedParticipant.peserta_offline_id}/block`" method="POST">
                        @csrf
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-secondary-800 mb-1">Blokir Peserta</h3>
                            <p class="text-sm text-secondary-500 mb-4">
                                <span x-text="selectedParticipant.nomor_peserta"></span> - <span x-text="selectedParticipant.nama_peserta"></span>
                            </p>

                            <div class="mb-4">
                                <label for="block_reason" class="block text-sm font-medium text-secondary-700 mb-1">
                                    Alasan Blokir <span class="text-danger-500">*</span>
                                </label>
                                <textarea name="reason" id="block_reason" rows="3"
                                          class="input w-full"
                                          placeholder="Contoh: Ketahuan menyontek, membawa HP, dll..."
                                          required></textarea>
                            </div>

                            <div class="bg-danger-50 border border-danger-200 rounded-lg p-3 mb-4">
                                <p class="text-sm text-danger-700">
                                    ⚠️ Peserta yang diblokir tidak akan bisa login kembali sampai di-unblock oleh admin.
                                    Sesi aktif juga akan di-force logout.
                                </p>
                            </div>
                        </div>
                        <div class="border-t border-secondary-200 p-4 flex justify-end gap-2">
                            <button type="button" @click="closeBlockModal()" class="btn btn-ghost">Batal</button>
                            <button type="submit" class="btn btn-danger">Blokir Peserta</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal: Extend Time -->
        <div x-show="showExtendModal" x-cloak
             style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 99999;">
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5);"
                 @click="closeExtendModal()"></div>
            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; padding: 1rem; pointer-events: none;">
                <div style="pointer-events: auto; width: 100%; max-width: 28rem; background-color: white; border-radius: 1rem;">
                    <form :action="`{{ url('/superadmin/ujian/' . $ujian->id . '/pengawas/attempt') }}/${selectedParticipant.attempt_id}/extend-time`" method="POST">
                        @csrf
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-secondary-800 mb-1">Tambah Waktu Ujian</h3>
                            <p class="text-sm text-secondary-500 mb-4">
                                <span x-text="selectedParticipant.nomor_peserta"></span> - <span x-text="selectedParticipant.nama_peserta"></span>
                            </p>

                            <div class="mb-4">
                                <label for="added_minutes" class="block text-sm font-medium text-secondary-700 mb-1">
                                    Tambahan Waktu (menit) <span class="text-danger-500">*</span>
                                </label>
                                <input type="number" name="added_minutes" id="added_minutes"
                                       class="input w-full"
                                       min="1" max="180" step="1"
                                       placeholder="Contoh: 15"
                                       required>
                                <p class="text-xs text-secondary-500 mt-1">Range: 1-180 menit</p>
                            </div>

                            <div class="mb-4">
                                <label for="extend_reason" class="block text-sm font-medium text-secondary-700 mb-1">
                                    Alasan <span class="text-danger-500">*</span>
                                </label>
                                <textarea name="reason" id="extend_reason" rows="3"
                                          class="input w-full"
                                          placeholder="Contoh: Peserta mengalami masalah teknis (koneksi terputus), listrik padam, dll..."
                                          required></textarea>
                            </div>

                            <div class="bg-primary-50 border border-primary-200 rounded-lg p-3 mb-4">
                                <p class="text-sm text-primary-700">
                                    ℹ️ Extension akan langsung berlaku dan tercatat di riwayat audit.
                                </p>
                            </div>
                        </div>
                        <div class="border-t border-secondary-200 p-4 flex justify-end gap-2">
                            <button type="button" @click="closeExtendModal()" class="btn btn-ghost">Batal</button>
                            <button type="submit" class="btn btn-primary">Tambah Waktu</button>
                        </div>
                    </form>
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
                showBlockModal: false,
                showExtendModal: false,
                selectedParticipant: {},

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

                openBlockModal(participant) {
                    this.selectedParticipant = participant;
                    this.showBlockModal = true;
                },

                closeBlockModal() {
                    this.showBlockModal = false;
                    this.selectedParticipant = {};
                },

                openExtendModal(participant) {
                    if (!participant.attempt_id) {
                        alert('Peserta belum memulai ujian, tidak bisa extend waktu.');
                        return;
                    }
                    this.selectedParticipant = participant;
                    this.showExtendModal = true;
                },

                closeExtendModal() {
                    this.showExtendModal = false;
                    this.selectedParticipant = {};
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
