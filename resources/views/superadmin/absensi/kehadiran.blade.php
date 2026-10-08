@extends('superadmin.layouts.app')

@section('title', 'Kelola Kehadiran')

@section('breadcrumb')
    <a href="{{ route('superadmin.absensi.index') }}" class="text-secondary-500 hover:text-secondary-700">Kelola Absensi</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.absensi.activation', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">Aktivasi Peserta</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Kelola Kehadiran</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Kelola Kehadiran</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('superadmin.absensi.activation', $ujian) }}" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Aktivasi Peserta
            </a>
            <a href="{{ route('superadmin.absensi.index') }}" class="btn btn-ghost">Kembali</a>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        @if(session('success'))
            <x-alert type="success" dismissible>{{ session('success') }}</x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger" dismissible>{{ session('error') }}</x-alert>
        @endif

        <div class="card">
            <div class="card-body">
                @if($peserta->isEmpty())
                    <div class="text-center py-8 text-secondary-500">
                        <p>Belum ada peserta untuk ujian ini.</p>
                    </div>
                @else
                    <form id="bulkForm" method="POST" action="{{ route('superadmin.absensi.kehadiran.bulk', $ujian) }}" class="space-y-4">
                        @csrf

                        <div class="flex justify-between items-center pb-4 border-b border-secondary-200">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="selectAll" class="w-4 h-4">
                                <span class="text-sm font-medium">Pilih Semua</span>
                            </label>

                            <div class="flex gap-2">
                                <button type="button" onclick="bulkUpdate('hadir')" class="btn btn-success btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Set Hadir (Terpilih)
                                </button>
                                <button type="button" onclick="bulkUpdate('tidak_hadir')" class="btn btn-secondary btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Set Tidak Hadir (Terpilih)
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-secondary-200">
                                        <th class="text-left py-3 px-4 w-10">
                                            <input type="checkbox" class="w-4 h-4 itemCheckbox">
                                        </th>
                                        <th class="text-left py-3 px-4 text-sm font-medium text-secondary-700">Nomor Peserta</th>
                                        <th class="text-left py-3 px-4 text-sm font-medium text-secondary-700">Nama Peserta</th>
                                        <th class="text-center py-3 px-4 text-sm font-medium text-secondary-700">Aktivasi</th>
                                        <th class="text-center py-3 px-4 text-sm font-medium text-secondary-700">Kehadiran</th>
                                        <th class="text-center py-3 px-4 text-sm font-medium text-secondary-700">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($peserta as $p)
                                        @php
                                            $kehadiran = $p->kehadiran->first();
                                            $isHadir = $kehadiran && $kehadiran->status_kehadiran === 'hadir';
                                        @endphp
                                        <tr class="border-b border-secondary-100 hover:bg-secondary-50">
                                            <td class="py-3 px-4">
                                                <input type="checkbox" name="peserta_ids[]" value="{{ $p->id }}" class="w-4 h-4 itemCheckbox">
                                            </td>
                                            <td class="py-3 px-4">
                                                <strong class="text-secondary-800">{{ $p->nomor_peserta }}</strong>
                                            </td>
                                            <td class="py-3 px-4 text-secondary-700">
                                                {{ $p->nama_peserta }}
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                @if($p->is_active)
                                                    <x-badge type="success">Aktif</x-badge>
                                                @else
                                                    <x-badge type="secondary">Nonaktif</x-badge>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                @if($isHadir)
                                                    <x-badge type="success">✓ Hadir</x-badge>
                                                @else
                                                    <x-badge type="secondary">Belum Hadir</x-badge>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <div class="inline-flex gap-2">
                                                    <button type="button"
                                                            @click="toggleKehadiran('{{ route('superadmin.absensi.kehadiran.update', [$ujian, $p]) }}', 'hadir')"
                                                            class="btn btn-sm {{ $isHadir ? 'btn-success' : 'btn-ghost' }}">
                                                        Hadir
                                                    </button>
                                                    <button type="button"
                                                            @click="toggleKehadiran('{{ route('superadmin.absensi.kehadiran.update', [$ujian, $p]) }}', 'tidak_hadir')"
                                                            class="btn btn-sm {{ !$isHadir ? 'btn-secondary' : 'btn-ghost' }}">
                                                        Tidak Hadir
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($peserta->hasPages())
                            <div class="mt-4">
                                {{ $peserta->links() }}
                            </div>
                        @endif
                    </form>

                    <form id="individualKehadiranForm" method="POST" style="display: none;">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status_kehadiran" id="individualStatus">
                    </form>

                    <script>
                        const selectAllCheckbox = document.getElementById('selectAll');
                        const itemCheckboxes = document.querySelectorAll('.itemCheckbox');

                        selectAllCheckbox?.addEventListener('change', function() {
                            itemCheckboxes.forEach(cb => {
                                cb.checked = this.checked;
                            });
                        });

                        itemCheckboxes.forEach(cb => {
                            cb.addEventListener('change', function() {
                                selectAllCheckbox.checked = Array.from(itemCheckboxes).every(c => c.checked);
                            });
                        });

                        function toggleKehadiran(actionUrl, status) {
                            const form = document.getElementById('individualKehadiranForm');
                            form.action = actionUrl;
                            document.getElementById('individualStatus').value = status;
                            form.submit();
                        }

                        function bulkUpdate(status) {
                            const checked = document.querySelectorAll('input[name="peserta_ids[]"]:checked');
                            if (checked.length === 0) {
                                alert('Pilih minimal satu peserta');
                                return;
                            }

                            const form = document.getElementById('bulkForm');
                            // Remove existing status input if any
                            const existingStatus = form.querySelector('input[name="status_kehadiran"]');
                            if (existingStatus) {
                                existingStatus.remove();
                            }

                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'status_kehadiran';
                            input.value = status;
                            form.appendChild(input);
                            form.submit();
                        }
                    </script>
                @endif
            </div>
        </div>
    </div>
@endsection
