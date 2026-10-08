@extends('superadmin.layouts.app')

@section('title', 'Aktivasi Peserta')

@section('breadcrumb')
    <a href="{{ route('superadmin.absensi.index') }}" class="text-secondary-500 hover:text-secondary-700">Kelola Absensi</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Aktivasi Peserta</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Aktivasi Peserta</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('superadmin.absensi.kehadiran', $ujian) }}" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Kelola Kehadiran
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
                    <form id="bulkForm" method="POST" action="{{ route('superadmin.absensi.bulk-toggle', $ujian) }}" class="space-y-4">
                        @csrf

                        <div class="flex justify-between items-center pb-4 border-b border-secondary-200">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" id="selectAll" class="w-4 h-4">
                                <span class="text-sm font-medium">Pilih Semua</span>
                            </label>

                            <div class="flex gap-2">
                                <button type="button" onclick="bulkToggle('activate')" class="btn btn-success btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Aktifkan (Terpilih)
                                </button>
                                <button type="button" onclick="bulkToggle('deactivate')" class="btn btn-secondary btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Nonaktifkan (Terpilih)
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
                                        <th class="text-center py-3 px-4 text-sm font-medium text-secondary-700">Status</th>
                                        <th class="text-center py-3 px-4 text-sm font-medium text-secondary-700">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($peserta as $p)
                                        <tr class="border-b border-secondary-100 hover:bg-secondary-50">
                                            <td class="py-3 px-4">
                                                <input type="checkbox" name="ids[]" value="{{ $p->id }}" class="w-4 h-4 itemCheckbox">
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
                                                 <button type="button"
                                                         @click="toggleIndividual('{{ route('superadmin.absensi.toggle', [$ujian, $p]) }}', '{{ $p->is_active ? 'deactivate' : 'activate' }}')"
                                                         class="btn btn-sm {{ $p->is_active ? 'btn-danger' : 'btn-success' }}">
                                                     {{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                 </button>
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

                     <form id="individualToggleForm" method="POST" style="display: none;">
                         @csrf
                         @method('PATCH')
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

                         function toggleIndividual(actionUrl, action) {
                             const form = document.getElementById('individualToggleForm');
                             form.action = actionUrl;
                             form.submit();
                         }

                         function bulkToggle(action) {
                             const checked = document.querySelectorAll('input[name="ids[]"]:checked');
                             if (checked.length === 0) {
                                 alert('Pilih minimal satu peserta');
                                 return;
                             }

                             const form = document.getElementById('bulkForm');
                             // Remove existing action input if any
                             const existingAction = form.querySelector('input[name="action"]');
                             if (existingAction) {
                                 existingAction.remove();
                             }
                             
                             const input = document.createElement('input');
                             input.type = 'hidden';
                             input.name = 'action';
                             input.value = action;
                             form.appendChild(input);
                             form.submit();
                         }
                     </script>
                @endif
            </div>
        </div>
    </div>
@endsection
