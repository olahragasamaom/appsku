@extends('superadmin.layouts.app')

@section('title', 'Manajemen Modul')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Manajemen Modul</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Manajemen Modul</h1>
            <p class="text-secondary-500 mt-1">Atur level user beserta hak akses (lihat, ubah, hapus) tiap modul</p>
        </div>
        <button type="button"
                @click="$dispatch('user-level-form', { mode: 'create' })"
                class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Level
        </button>
    </div>
@endsection

@php
    $actionLabels = ['view' => 'Lihat', 'edit' => 'Ubah', 'delete' => 'Hapus'];
@endphp

@section('content')
    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Level</th>
                    <th class="px-6 py-3 text-left">Keterangan</th>
                    <th class="px-6 py-3 text-center">Jumlah Modul</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>

                @forelse($userLevels as $userLevel)
                    @php($permitted = $userLevel->permittedActions())
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-secondary-900">{{ $userLevel->nama }}</p>
                            <p class="text-xs text-secondary-400">{{ $userLevel->slug }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-secondary-600">{{ $userLevel->keterangan ?: '-' }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-semibold text-secondary-900">{{ count($permitted) }}</span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($userLevel->is_active)
                                <x-badge type="success">Aktif</x-badge>
                            @else
                                <x-badge type="secondary">Nonaktif</x-badge>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                        @click="$dispatch('user-level-form', {
                                            mode: 'edit',
                                            action: '{{ route('superadmin.user-levels.update', $userLevel) }}',
                                            nama: {{ Js::from($userLevel->nama) }},
                                            keterangan: {{ Js::from($userLevel->keterangan ?? '') }},
                                            isActive: {{ $userLevel->is_active ? 'true' : 'false' }},
                                            permitted: {{ Js::from($permitted) }}
                                        })"
                                        class="btn btn-ghost btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button type="button"
                                        @click="$dispatch('confirm-dialog', {
                                            title: 'Hapus Level',
                                            message: 'Apakah Anda yakin ingin menghapus level {{ $userLevel->nama }}?',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger',
                                            formAction: '{{ route('superadmin.user-levels.destroy', $userLevel) }}'
                                        })"
                                        class="btn btn-ghost btn-sm text-danger-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-secondary-500">
                            Belum ada level user yang dibuat
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($userLevels->hasPages())
            <div class="card-footer">
                {{ $userLevels->links() }}
            </div>
        @endif
    </div>

    <div
        x-data="{
            open: {{ $errors->any() ? 'true' : 'false' }},
            mode: '{{ old('_form_mode', 'create') }}',
            action: '{{ old('_form_action', route('superadmin.user-levels.store')) }}',
            nama: {{ Js::from(old('nama', '')) }},
            keterangan: {{ Js::from(old('keterangan', '')) }},
            isActive: {{ old('is_active', true) ? 'true' : 'false' }},
            actions: {{ Js::from($actions) }},
            selected: {},
            initSelected(permitted) {
                const map = {};
                Object.keys(permitted || {}).forEach(key => {
                    map[key] = {};
                    (permitted[key] || []).forEach(action => { map[key][action] = true; });
                });
                this.selected = map;
            },
            isChecked(key, action) {
                return !!(this.selected[key] && this.selected[key][action]);
            },
            toggle(key, action) {
                if (!this.selected[key]) this.selected[key] = {};
                this.selected[key][action] = !this.selected[key][action];
                // Selecting edit/delete implies view.
                if (action !== 'view' && this.selected[key][action]) {
                    this.selected[key]['view'] = true;
                }
            },
            toggleModuleAll(key) {
                const all = this.actions.every(a => this.isChecked(key, a));
                if (!this.selected[key]) this.selected[key] = {};
                this.actions.forEach(a => { this.selected[key][a] = !all; });
            },
            checkedPermissionNames() {
                const names = [];
                Object.keys(this.selected).forEach(key => {
                    Object.keys(this.selected[key]).forEach(action => {
                        if (this.selected[key][action]) names.push(key + '.' + action);
                    });
                });
                return names;
            },
            show(detail) {
                this.mode = detail.mode;
                this.action = detail.mode === 'edit' ? detail.action : '{{ route('superadmin.user-levels.store') }}';
                this.nama = detail.mode === 'edit' ? detail.nama : '';
                this.keterangan = detail.mode === 'edit' ? detail.keterangan : '';
                this.isActive = detail.mode === 'edit' ? detail.isActive : true;
                this.initSelected(detail.mode === 'edit' ? detail.permitted : {});
                this.open = true;
                this.$nextTick(() => this.$refs.namaInput.focus());
            }
        }"
        x-init="initSelected({{ Js::from(collect(old('permissions', []))->reduce(function ($carry, $name) {
            if (str_contains($name, '.')) {
                [$k, $a] = explode('.', $name, 2);
                $carry[$k][] = $a;
            }
            return $carry;
        }, [])) }})"
        x-on:user-level-form.window="show($event.detail)"
        x-on:keydown.escape.window="open = false"
        x-show="open"
        x-cloak
        class="modal-backdrop"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div
            @click.outside="open = false"
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="modal"
            style="max-width: 48rem;"
        >
            <div class="modal-header">
                <h3 class="modal-title" x-text="mode === 'edit' ? 'Edit Level' : 'Tambah Level'"></h3>
                <button type="button" @click="open = false" class="modal-close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="action" method="POST">
                @csrf
                <template x-if="mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="_form_mode" :value="mode">
                <input type="hidden" name="_form_action" :value="action">

                <div class="modal-body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="nama" class="block text-sm font-medium text-secondary-700 mb-1">
                                Nama Level <span class="text-danger-500">*</span>
                            </label>
                            <input type="text" name="nama" id="nama"
                                   x-ref="namaInput" x-model="nama"
                                   class="input w-full @error('nama') border-danger-500 @enderror"
                                   placeholder="Operator" required>
                            @error('nama')
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="is_active" class="block text-sm font-medium text-secondary-700 mb-1">
                                Status
                            </label>
                            <select name="is_active" id="is_active" x-model="isActive"
                                    class="input w-full @error('is_active') border-danger-500 @enderror">
                                <option value="true">Aktif</option>
                                <option value="false">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="keterangan" class="block text-sm font-medium text-secondary-700 mb-1">
                            Keterangan
                        </label>
                        <textarea name="keterangan" id="keterangan" rows="2"
                                  x-model="keterangan"
                                  class="input w-full @error('keterangan') border-danger-500 @enderror"
                                  placeholder="Keterangan level (opsional)"></textarea>
                        @error('keterangan')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-5">
                        <label class="block text-sm font-medium text-secondary-700 mb-2">
                            Hak Akses Modul
                        </label>
                        @error('permissions')
                            <p class="mb-2 text-sm text-danger-600">{{ $message }}</p>
                        @enderror

                        {{-- Hidden inputs generated from Alpine state so only checked permissions are submitted --}}
                        <template x-for="name in checkedPermissionNames()" :key="name">
                            <input type="hidden" name="permissions[]" :value="name">
                        </template>

                        <div class="space-y-5 max-h-80 overflow-y-auto border border-secondary-200 rounded-lg p-3">
                            @foreach($modules as $grup => $items)
                                <div>
                                    <p class="text-xs font-bold text-secondary-400 uppercase tracking-widest mb-2">{{ $grup ?: 'Lainnya' }}</p>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="text-secondary-500">
                                                    <th class="text-left font-medium py-1">Modul</th>
                                                    @foreach($actionLabels as $key => $label)
                                                        <th class="text-center font-medium py-1 w-16">{{ $label }}</th>
                                                    @endforeach
                                                    <th class="text-center font-medium py-1 w-14">Semua</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $module)
                                                    <tr class="border-t border-secondary-100">
                                                        <td class="py-2 text-secondary-700">{{ $module->label }}</td>
                                                        @foreach(array_keys($actionLabels) as $action)
                                                            <td class="text-center py-2">
                                                                <input type="checkbox"
                                                                       :checked="isChecked('{{ $module->key }}', '{{ $action }}')"
                                                                       @change="toggle('{{ $module->key }}', '{{ $action }}')"
                                                                       class="rounded border-secondary-300 text-primary-600 focus:ring-primary-500">
                                                            </td>
                                                        @endforeach
                                                        <td class="text-center py-2">
                                                            <button type="button" @click="toggleModuleAll('{{ $module->key }}')"
                                                                    class="text-xs text-primary-600 hover:underline">Toggle</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-secondary-400">Catatan: memilih "Ubah" atau "Hapus" otomatis mengaktifkan "Lihat".</p>
                    </div>
                </div>

                <div class="modal-footer flex items-center justify-end gap-3">
                    <button type="button" @click="open = false" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary"
                            x-text="mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Level'"></button>
                </div>
            </form>
        </div>
    </div>
@endsection
