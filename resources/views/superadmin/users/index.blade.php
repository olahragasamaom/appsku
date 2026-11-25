@extends('superadmin.layouts.app')

@section('title', 'Manajemen User')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Manajemen User</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Manajemen User</h1>
            <p class="text-secondary-500 mt-1">Kelola daftar user sistem (bukan peserta ujian)</p>
        </div>
        <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah User
        </a>
    </div>
@endsection

@section('content')
    <div class="card mb-6">
        <div class="card-body-sm">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="input flex-1" placeholder="Cari nama / email / username...">
                <button type="submit" class="btn btn-secondary">Cari</button>
            </form>
        </div>
    </div>

    {{-- Portal Login Quick Reference --}}
    <div class="card mb-6 bg-slate-50 border-secondary-200" x-data="{ open: false }">
        <div class="p-4 flex items-center justify-between cursor-pointer select-none" @click="open = !open">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-secondary-900">Daftar Link Akses Login per Level User</h3>
                    <p class="text-xs text-secondary-500">Klik untuk melihat tautan halaman login masing-masing portal role</p>
                </div>
            </div>
            <button type="button" class="text-secondary-400 hover:text-secondary-600 p-1">
                <svg class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
        <div x-show="open" x-cloak class="px-4 pb-4 pt-1 border-t border-secondary-200/60">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 text-xs">
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-secondary-200">
                    <div>
                        <span class="font-semibold text-secondary-800">Pembuat Soal</span>
                        <div class="font-mono text-secondary-500 text-[11px] mt-0.5">/pembuat-soal/login</div>
                    </div>
                    <a href="{{ url('/pembuat-soal/login') }}" target="_blank" class="btn btn-ghost btn-xs text-primary-600 hover:text-primary-800">Buka &rarr;</a>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-secondary-200">
                    <div>
                        <span class="font-semibold text-secondary-800">Pengajar</span>
                        <div class="font-mono text-secondary-500 text-[11px] mt-0.5">/pengajar/login-pengajar</div>
                    </div>
                    <a href="{{ url('/pengajar/login-pengajar') }}" target="_blank" class="btn btn-ghost btn-xs text-primary-600 hover:text-primary-800">Buka &rarr;</a>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-secondary-200">
                    <div>
                        <span class="font-semibold text-secondary-800">Keuangan</span>
                        <div class="font-mono text-secondary-500 text-[11px] mt-0.5">/keuangan/login</div>
                    </div>
                    <a href="{{ url('/keuangan/login') }}" target="_blank" class="btn btn-ghost btn-xs text-primary-600 hover:text-primary-800">Buka &rarr;</a>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-secondary-200">
                    <div>
                        <span class="font-semibold text-secondary-800">Pengawas Ujian</span>
                        <div class="font-mono text-secondary-500 text-[11px] mt-0.5">/pengawas/login</div>
                    </div>
                    <a href="{{ url('/pengawas/login') }}" target="_blank" class="btn btn-ghost btn-xs text-primary-600 hover:text-primary-800">Buka &rarr;</a>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-secondary-200">
                    <div>
                        <span class="font-semibold text-secondary-800">Peserta Online</span>
                        <div class="font-mono text-secondary-500 text-[11px] mt-0.5">/peserta/login</div>
                    </div>
                    <a href="{{ url('/peserta/login') }}" target="_blank" class="btn btn-ghost btn-xs text-primary-600 hover:text-primary-800">Buka &rarr;</a>
                </div>
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-secondary-200">
                    <div>
                        <span class="font-semibold text-secondary-800">Peserta Offline</span>
                        <div class="font-mono text-secondary-500 text-[11px] mt-0.5">/peserta/ujian-offline</div>
                    </div>
                    <a href="{{ url('/peserta/ujian-offline') }}" target="_blank" class="btn btn-ghost btn-xs text-primary-600 hover:text-primary-800">Buka &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Nama</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-left">Username</th>
                    <th class="px-6 py-3 text-center">Tipe User</th>
                    <th class="px-6 py-3 text-center">Status</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>

                @forelse($users as $user)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4 font-medium text-secondary-900">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-secondary-600 text-sm">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-secondary-600 text-sm">{{ $user->username ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <div>
                                <x-badge :type="$user->tipe_user_badge">{{ $user->tipe_user }}</x-badge>
                            </div>
                            <div class="mt-1">
                                <a href="{{ $user->login_url }}" target="_blank"
                                   title="Buka portal login: {{ $user->login_url }}"
                                   class="inline-flex items-center gap-1 text-[11px] font-mono text-secondary-400 hover:text-primary-600 transition-colors">
                                    <span>{{ $user->login_path }}</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($user->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-700">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('superadmin.users.show', $user) }}" class="btn btn-ghost btn-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-secondary-500">
                            Tidak ada data user ditemukan
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $users->links() }}
    </div>
@endsection
