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
                            @if($user->is_superadmin)
                                <x-badge type="danger">Superadmin</x-badge>
                            @elseif($user->company_id)
                                <x-badge type="info">Company Admin</x-badge>
                            @else
                                <x-badge type="secondary">User</x-badge>
                            @endif
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
