@extends('superadmin.layouts.app')

@section('title', 'Manajemen Peserta - '.$ujian->nama_ujian)

@php
    function formatTime($detik) {
        $detik = (int) $detik;
        $jam = floor($detik / 3600);
        $menit = floor(($detik % 3600) / 60);
        $detik = $detik % 60;
        
        if ($jam > 0) {
            return "{$jam}h {$menit}m {$detik}s";
        } elseif ($menit > 0) {
            return "{$menit}m {$detik}s";
        } else {
            return "{$detik}s";
        }
    }
    
    $pesertaSedangUjianJson = collect($pesertaSedangUjian)->map(function ($p) {
        return [
            'id' => $p['id'],
            'nama' => $p['nama'],
            'nomor_peserta' => $p['nomor_peserta'],
            'is_paused' => $p['is_paused'],
            'sisa_detik' => $p['sisa_detik'],
        ];
    })->keyBy('id');
@endphp

@section('breadcrumb')
    <a href="{{ route('superadmin.hasil-ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Hasil Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">{{ $ujian->nama_ujian }}</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Manajemen Peserta</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Manajemen Peserta</h2>
            <p class="text-sm text-secondary-500">Pause/Resume waktu ujian peserta</p>
        </div>
        <a href="{{ route('superadmin.hasil-ujian.show', $ujian) }}" class="btn btn-ghost">Kembali</a>
    </div>
@endsection

@section('content')
    <div x-data="managementPage()" @init="init()" @destroy="stopAutoRefresh()" class="max-w-6xl mx-auto space-y-6">
        @if(session('success'))
            <x-alert type="success" dismissible>{{ session('success') }}</x-alert>
        @endif
        @if(session('error'))
            <x-alert type="error" dismissible>{{ session('error') }}</x-alert>
        @endif

        {{-- Summary --}}
        <div class="card bg-warning-50 border-warning-100">
            <div class="card-body">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-warning-900 mb-1">Peserta Sedang Ujian</h3>
                        <p class="text-sm text-warning-700">Total: <span class="font-bold">{{ count($pesertaSedangUjian) }}</span> peserta</p>
                        <p class="text-xs text-warning-600 mt-2">Superadmin dapat menghentikan sementara waktu ujian peserta jika terjadi kendala (misal: mati lampu). Sisa waktu akan disimpan.</p>
                    </div>
                </div>
            </div>
        </div>

        @if(count($pesertaSedangUjian) > 0)
            <div class="card">
                <div class="card-body-sm">
                    <x-table>
                        <x-slot name="header">
                            <th class="px-6 py-3 text-left">Nomor Peserta</th>
                            <th class="px-6 py-3 text-left">Nama Peserta</th>
                            <th class="px-6 py-3 text-center">Waktu Mulai</th>
                            <th class="px-6 py-3 text-center">Sisa Waktu</th>
                            <th class="px-6 py-3 text-center">Soal Terisi</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </x-slot>
                        @foreach($pesertaSedangUjian as $p)
                            <tr class="hover:bg-secondary-50" id="row-peserta-{{ $p['id'] }}">
                                <td class="px-6 py-4 font-mono font-semibold text-secondary-900">{{ $p['nomor_peserta'] ?? '-' }}</td>
                                <td class="px-6 py-4 text-secondary-700">{{ $p['nama'] }}</td>
                                <td class="px-6 py-4 text-center text-sm text-secondary-600">
                                    {{ $p['waktu_mulai']->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-sm" id="sisa-waktu-{{ $p['id'] }}" data-sisa-detik="{{ $p['sisa_detik'] }}" data-peserta-id="{{ $p['id'] }}">
                                    @if($p['is_paused'])
                                        <span class="text-danger-600 font-semibold">{{ formatTime($p['sisa_detik_saat_pause'] ?? 0) }} (paused)</span>
                                    @else
                                        <span class="text-slate-700 font-semibold">{{ formatTime($p['sisa_detik']) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-sm text-secondary-600">
                                    <span id="soal-terisi-{{ $p['id'] }}">{{ $p['jumlah_jawaban'] ?? 0 }}/{{ $p['total_soal'] ?? 0 }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($p['is_paused'])
                                        <x-badge type="danger">Terhenti</x-badge>
                                    @else
                                        <x-badge type="warning">Sedang Ujian</x-badge>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex gap-2">
                                         @if($p['is_paused'])
                                             <button type="button" onclick="openResumeModal({{ $p['id'] }}, '{{ addslashes($p['nama']) }}')" class="btn btn-sm btn-success">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Lanjut
                                            </button>
                                         @else
                                             <button type="button" onclick="openPauseConfirmModal({{ $p['id'] }}, '{{ addslashes($p['nama']) }}')" class="btn btn-sm btn-warning">
                                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                                 </svg>
                                                 Pause
                                             </button>
                                         @endif
                                         <button type="button" onclick="openStopConfirmModal({{ $p['id'] }}, '{{ addslashes($p['nama']) }}')" class="btn btn-sm btn-danger">
                                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4a2 2 0 012-2h4a1 1 0 011 1v2h5a1 1 0 011 1v1H4V7a1 1 0 011-1h2V4z"/>
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a1 1 0 011-1h14a1 1 0 011 1v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7z"/>
                                             </svg>
                                             Stop
                                         </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                </div>
            </div>
        @else
            <div class="card bg-slate-50 border-slate-200">
                <div class="card-body text-center">
                    <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5-4a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-slate-600">Tidak ada peserta yang sedang ujian</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal Konfirmasi Pause --}}
    <div id="pauseConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="bg-gradient-to-r from-warning-600 to-warning-700 px-6 py-4 text-white">
                <h3 class="font-semibold text-lg">Konfirmasi Pause Ujian</h3>
            </div>
            <div class="p-6">
                <div class="mb-4 p-4 bg-warning-50 border border-warning-200 rounded-lg">
                    <p class="text-sm text-warning-700">
                        <span class="font-semibold">Ujian akan dihentikan sementara.</span> Peserta akan logout dan dapat login kembali untuk melanjutkan dengan sisa waktu yang tersedia saat ini.
                    </p>
                </div>
                <p class="text-secondary-700 mb-4">Peserta: <span class="font-semibold" id="pauseConfirmModalPeserta"></span></p>
                <p class="text-secondary-700 mb-6">Sisa Waktu: <span class="font-mono font-semibold" id="pauseConfirmModalSisaWaktu"></span></p>

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('pauseConfirmModal')" class="btn btn-ghost flex-1">Batal</button>
                    <button type="button" onclick="submitPauseDirectly()" class="btn btn-warning flex-1">Ya, Pause Ujian</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Stop (Force Finalize) --}}
    <div id="stopConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="bg-gradient-to-r from-danger-600 to-danger-700 px-6 py-4 text-white">
                <h3 class="font-semibold text-lg">Konfirmasi Stop Ujian</h3>
            </div>
            <div class="p-6">
                <div class="mb-4 p-4 bg-danger-50 border border-danger-200 rounded-lg">
                    <p class="text-sm text-danger-700">
                        <span class="font-semibold">Ujian akan dihentikan permanen.</span> Hasil ujian akan disimpan dan peserta <span class="font-semibold">tidak dapat</span> melanjutkan ujian kembali.
                    </p>
                </div>
                <p class="text-secondary-700 mb-6">Peserta: <span class="font-semibold" id="stopConfirmModalPeserta"></span></p>

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('stopConfirmModal')" class="btn btn-ghost flex-1">Batal</button>
                    <button type="button" onclick="submitStop()" class="btn btn-danger flex-1">Ya, Stop Ujian</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Resume --}}
    <div id="resumeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="bg-gradient-to-r from-success-600 to-success-700 px-6 py-4 text-white">
                <h3 class="font-semibold text-lg">Lanjutkan Ujian Peserta</h3>
            </div>
            <div class="p-6">
                <div class="mb-4 p-4 bg-success-50 border border-success-200 rounded-lg">
                    <p class="text-sm text-success-700">
                        Peserta dapat melanjutkan ujian dengan sisa waktu yang tersedia.
                    </p>
                </div>
                <p class="text-secondary-700 mb-6">Nama Peserta: <span class="font-semibold" id="resumeModalPeserta"></span></p>

                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('resumeModal')" class="btn btn-ghost flex-1">Batal</button>
                    <button type="button" onclick="submitResume()" class="btn btn-success flex-1">Ya, Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global functions untuk modal
        let pauseConfirmModalId = null;
        let stopConfirmModalId = null;
        let resumeModalId = null;
        let appState = null;

        function showModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function openPauseConfirmModal(pesertaId, pesertaNama) {
            pauseConfirmModalId = pesertaId;
            document.getElementById('pauseConfirmModalPeserta').textContent = pesertaNama;
            
            const sisaWaktuCell = document.getElementById(`sisa-waktu-${pesertaId}`);
            const sisaWaktuText = sisaWaktuCell ? sisaWaktuCell.textContent.trim() : '-';
            document.getElementById('pauseConfirmModalSisaWaktu').textContent = sisaWaktuText;
            
            showModal('pauseConfirmModal');
        }

        function openStopConfirmModal(pesertaId, pesertaNama) {
            stopConfirmModalId = pesertaId;
            document.getElementById('stopConfirmModalPeserta').textContent = pesertaNama;
            showModal('stopConfirmModal');
        }

        function openResumeModal(pesertaId, pesertaNama) {
            resumeModalId = pesertaId;
            document.getElementById('resumeModalPeserta').textContent = pesertaNama;
            showModal('resumeModal');
        }

        function submitPauseDirectly() {
            const sisaWaktuText = document.getElementById('pauseConfirmModalSisaWaktu').textContent.trim();
            
            let sisaDetik = 0;
            const jamMatch = sisaWaktuText.match(/(\d+)h/);
            const menitMatch = sisaWaktuText.match(/(\d+)m/);
            const detikMatch = sisaWaktuText.match(/(\d+)s/);
            
            if (jamMatch) sisaDetik += parseInt(jamMatch[1]) * 3600;
            if (menitMatch) sisaDetik += parseInt(menitMatch[1]) * 60;
            if (detikMatch) sisaDetik += parseInt(detikMatch[1]);
            
            const ujianId = {{ $ujian->id }};
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/superadmin/hasil-ujian/${ujianId}/peserta/${pauseConfirmModalId}/toggle-pause`;
            form.innerHTML = `@csrf
                <input type="hidden" name="sisa_detik" value="${sisaDetik}">`;
            document.body.appendChild(form);
            form.submit();
        }

        function submitStop() {
            const ujianId = {{ $ujian->id }};
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/superadmin/hasil-ujian/${ujianId}/peserta/${stopConfirmModalId}/force-stop`;
            form.innerHTML = `@csrf`;
            document.body.appendChild(form);
            form.submit();
        }

        function submitResume() {
            const ujianId = {{ $ujian->id }};
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/superadmin/hasil-ujian/${ujianId}/peserta/${resumeModalId}/toggle-pause`;
            form.innerHTML = `@csrf`;
            document.body.appendChild(form);
            form.submit();
        }

        function managementPage() {
            const ujianId = {{ $ujian->id }};
            const pesertaData = {!! json_encode($pesertaSedangUjianJson) !!};
            
            return {
                showPauseConfirmModal: false,
                showPauseInputModal: false,
                showStopConfirmModal: false,
                showResumeConfirmModal: false,
                pauseConfirmModalId: null,
                pauseConfirmModalPeserta: '',
                pauseInputModalId: null,
                pauseInputModalPeserta: '',
                stopConfirmModalId: null,
                stopConfirmModalPeserta: '',
                resumeModalId: null,
                resumeModalPeserta: '',
                sisaDetikInput: 0,
                pauseFormAction: '',
                stopFormAction: '',
                resumeFormAction: '',
                refreshInterval: null,
                tickInterval: null,
                lastSyncTime: Date.now(),

                init() {
                    appState = this;
                    this.pauseFormAction = '';
                    this.stopFormAction = '';
                    this.resumeFormAction = '';
                    this.startAutoRefresh();
                    this.startCountdownTick();
                },

                openPauseConfirmModal(pesertaId, pesertaNama) {
                    this.pauseConfirmModalId = pesertaId;
                    this.pauseConfirmModalPeserta = pesertaNama;
                    this.showPauseConfirmModal = true;
                },

                openPauseInputModal(pesertaId, pesertaNama) {
                    this.showPauseConfirmModal = false;
                    this.pauseInputModalId = pesertaId;
                    this.pauseInputModalPeserta = pesertaNama;
                    this.sisaDetikInput = 0;
                    this.pauseFormAction = `/superadmin/hasil-ujian/${ujianId}/peserta/${pesertaId}/toggle-pause`;
                    this.showPauseInputModal = true;
                    
                    setTimeout(() => {
                        const input = document.querySelector('input[type="number"][name="sisa_detik"]');
                        if (input) input.focus();
                    }, 100);
                },

                openStopConfirmModal(pesertaId, pesertaNama) {
                    this.stopConfirmModalId = pesertaId;
                    this.stopConfirmModalPeserta = pesertaNama;
                    this.stopFormAction = `/superadmin/hasil-ujian/${ujianId}/peserta/${pesertaId}/force-stop`;
                    this.showStopConfirmModal = true;
                },

                openResumeModal(pesertaId, pesertaNama) {
                    this.resumeModalId = pesertaId;
                    this.resumeModalPeserta = pesertaNama;
                    this.resumeFormAction = `/superadmin/hasil-ujian/${ujianId}/peserta/${pesertaId}/toggle-pause`;
                    this.showResumeConfirmModal = true;
                },

                submitPause() {
                    if (this.sisaDetikInput < 0) {
                        alert('Sisa waktu harus 0 atau lebih');
                        return;
                    }
                    const form = document.getElementById('pauseForm');
                    form.action = this.pauseFormAction;
                    form.submit();
                },

                submitResume() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.resumeFormAction;
                    form.innerHTML = `@csrf`;
                    document.body.appendChild(form);
                    form.submit();
                },

                submitStop() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.stopFormAction;
                    form.innerHTML = `@csrf`;
                    document.body.appendChild(form);
                    form.submit();
                },

                startAutoRefresh() {
                    this.refreshInterval = setInterval(() => {
                        this.refreshData();
                    }, 60000);
                },

                startCountdownTick() {
                    this.tickInterval = setInterval(() => {
                        this.updateCountdown();
                    }, 1000);
                },

                stopAutoRefresh() {
                    if (this.refreshInterval) clearInterval(this.refreshInterval);
                    if (this.tickInterval) clearInterval(this.tickInterval);
                },

                formatTime(detik) {
                    detik = Math.max(0, Math.floor(detik));
                    const jam = Math.floor(detik / 3600);
                    const menit = Math.floor((detik % 3600) / 60);
                    const sisa = detik % 60;
                    
                    if (jam > 0) {
                        return `${jam}h ${menit}m ${sisa}s`;
                    } else if (menit > 0) {
                        return `${menit}m ${sisa}s`;
                    } else {
                        return `${sisa}s`;
                    }
                },

                updateCountdown() {
                    Object.entries(pesertaData).forEach(([pesertaId, p]) => {
                        const cell = document.getElementById(`sisa-waktu-${pesertaId}`);
                        if (!cell) return;
                        
                        if (p.is_paused) return;
                        
                        const elapsedMs = Date.now() - this.lastSyncTime;
                        const elapsedDetik = Math.floor(elapsedMs / 1000);
                        const currentSisaDetik = Math.max(0, p.sisa_detik - elapsedDetik);
                        
                        const span = cell.querySelector('span');
                        if (span && !span.textContent.includes('paused')) {
                            span.textContent = this.formatTime(currentSisaDetik);
                        }
                    });
                },

                async refreshData() {
                    try {
                        const response = await fetch(`/superadmin/hasil-ujian/${ujianId}/management/data`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!response.ok) return;
                        
                        const data = await response.json();
                        this.updateTableData(data.peserta);
                        this.lastSyncTime = Date.now();
                    } catch (e) {
                        console.warn('Auto-refresh gagal:', e);
                    }
                },

                updateTableData(peserta) {
                    peserta.forEach(p => {
                        pesertaData[p.id] = p;
                        const sisaWaktuCell = document.getElementById(`sisa-waktu-${p.id}`);
                        if (sisaWaktuCell) {
                            if (p.is_paused) {
                                sisaWaktuCell.innerHTML = `<span class="text-danger-600 font-semibold">${this.formatTime(p.sisa_detik_saat_pause || 0)} (paused)</span>`;
                            } else {
                                sisaWaktuCell.innerHTML = `<span class="text-slate-700 font-semibold">${this.formatTime(p.sisa_detik)}</span>`;
                            }
                        }
                    });
                }
            };
        }
    </script>
@endsection
