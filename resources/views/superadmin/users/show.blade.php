@extends('superadmin.layouts.app')

@section('title', $user->name)

@section('breadcrumb')
    <a href="{{ route('superadmin.users.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen User</a>
    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-secondary-900 font-medium">{{ $user->name }}</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">{{ $user->name }}</h1>
            <p class="text-secondary-500 mt-1">Detail informasi user</p>
        </div>
        <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Info --}}
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Umum</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Nama</label>
                            <p class="text-secondary-900 font-medium">{{ $user->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Email</label>
                            <p class="text-secondary-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Username</label>
                            <p class="text-secondary-900">{{ $user->username ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-1">No. Telepon</label>
                            <p class="text-secondary-900">{{ $user->phone ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Tipe User</label>
                            <div>
                                <x-badge :type="$user->tipe_user_badge">{{ $user->tipe_user }}</x-badge>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Status</label>
                            <div>
                                @if($user->is_active)
                                    <x-badge type="success">Aktif</x-badge>
                                @else
                                    <x-badge type="warning">Nonaktif</x-badge>
                                @endif
                            </div>
                        </div>
                        @if($user->company_id)
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 mb-1">Perusahaan</label>
                                <p class="text-secondary-900">{{ $user->company?->name ?? '-' }}</p>
                            </div>
                        @endif
                        @if($user->user_level_id)
                            <div>
                                <label class="block text-sm font-medium text-secondary-700 mb-1">User Level</label>
                                <p class="text-secondary-900">{{ $user->userLevel?->nama ?? '-' }}</p>
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Dibuat Pada</label>
                            <p class="text-secondary-900">{{ $user->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Terakhir Diupdate</label>
                            <p class="text-secondary-900">{{ $user->updated_at->format('d M Y H:i') }}</p>
                        </div>
                        <div class="sm:col-span-2 pt-4 border-t border-secondary-100" x-data="{ copied: false }">
                            <label class="block text-sm font-medium text-secondary-700 mb-1">Akses Halaman Login</label>
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ $user->login_url }}" target="_blank"
                                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium bg-primary-50 text-primary-700 hover:bg-primary-100 hover:text-primary-800 transition-colors border border-primary-200">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    <span class="font-mono text-xs font-semibold">{{ $user->login_url }}</span>
                                </a>
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $user->login_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="btn btn-secondary btn-sm inline-flex items-center gap-1.5"
                                        title="Salin URL Login">
                                    <svg x-show="!copied" class="w-4 h-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <svg x-show="copied" x-cloak class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span x-text="copied ? 'Tersalin!' : 'Salin URL'"></span>
                                </button>
                            </div>
                            <p class="text-xs text-secondary-500 mt-1.5">User dengan tipe <strong>{{ $user->tipe_user }}</strong> diarahkan login melalui portal di atas.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Roles --}}
            @if($user->roles->count() > 0)
                <div class="card mt-6">
                    <div class="card-header">
                        <h3 class="card-title">Roles & Permission</h3>
                    </div>
                    <div class="card-body">
                        <div class="flex flex-wrap gap-2">
                            @foreach($user->roles as $role)
                                <x-badge type="info">{{ $role->name }}</x-badge>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Action Buttons --}}
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aksi</h3>
                </div>
                <div class="card-body space-y-3">
                    <a href="{{ $user->login_url }}" target="_blank"
                       class="btn btn-secondary w-full inline-flex justify-center items-center gap-2">
                        <svg class="w-4 h-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Buka Portal Login
                    </a>
                    <a href="{{ route('superadmin.users.edit-password', $user) }}"
                       class="btn btn-primary w-full inline-flex justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Reset Password
                    </a>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status Verifikasi</h3>
                </div>
                <div class="card-body space-y-4">
                    <div class="flex items-start gap-3">
                        @if($user->email_verified_at)
                            <svg class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-medium text-secondary-900">Email Terverifikasi</p>
                                <p class="text-sm text-secondary-600">{{ $user->email_verified_at->format('d M Y H:i') }}</p>
                            </div>
                        @else
                            <svg class="w-5 h-5 text-warning-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="font-medium text-secondary-900">Belum Terverifikasi</p>
                                <p class="text-sm text-secondary-600">Email belum diverifikasi</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
