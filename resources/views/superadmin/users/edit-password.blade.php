@extends('superadmin.layouts.app')

@section('title', 'Reset Password - ' . $user->name)

@section('breadcrumb')
    <a href="{{ route('superadmin.users.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen User</a>
    <a href="{{ route('superadmin.users.show', $user) }}" class="text-secondary-500 hover:text-secondary-700">{{ $user->name }}</a>
    <span class="text-secondary-900 font-medium">Reset Password</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Reset Password</h1>
            <p class="text-secondary-500 mt-1">Ubah password untuk {{ $user->name }}</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="card max-w-2xl">
        <div class="card-body">
            <form method="POST" action="{{ route('superadmin.users.update-password', $user) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- User Info -->
                <div class="bg-secondary-50 rounded-lg p-4 mb-6">
                    <p class="text-secondary-500 text-sm">User yang akan direset password</p>
                    <p class="text-secondary-900 font-semibold mt-1">{{ $user->name }}</p>
                    <p class="text-secondary-600 text-sm mt-1">{{ $user->email }} / {{ $user->username }}</p>
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-secondary-700 mb-2">
                        Password Baru *
                    </label>
                    <input type="password" id="password" name="password" required
                           class="form-input w-full @error('password') border-danger-500 @enderror"
                           placeholder="Masukkan password baru (minimal 8 karakter)">
                    @error('password')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-secondary-700 mb-2">
                        Konfirmasi Password *
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="form-input w-full @error('password_confirmation') border-danger-500 @enderror"
                           placeholder="Konfirmasi password baru">
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Warning -->
                <div class="bg-warning-50 border border-warning-200 rounded-lg p-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-warning-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-6v-2m0 0V7a2 2 0 012-2h2.586a1 1 0 00-.707-1.293m-5.242 1.293A2 2 0 0010.414 2H12a2 2 0 012 2v2.586M19 12a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-warning-800">Password akan langsung berubah</p>
                            <p class="text-sm text-warning-700 mt-1">User perlu login kembali dengan password baru ini</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-6 border-t border-secondary-200">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin mengubah password user ini?')">
                        Reset Password
                    </button>
                    <a href="{{ route('superadmin.users.show', $user) }}" class="btn btn-ghost">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
