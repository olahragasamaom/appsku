@extends('superadmin.layouts.app')

@section('title', 'Ganti Password')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Ganti Password</span>
@endsection

@section('header')
    <div>
        <h1 class="text-2xl font-bold text-secondary-900">Ganti Password</h1>
        <p class="text-secondary-500 mt-1">Ubah password akun superadmin Anda</p>
    </div>
@endsection

@section('content')
    <div class="max-w-2xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Password Baru</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('superadmin.change-password.update') }}">
                    @csrf
                    @method('PUT')

                    {{-- Current Password --}}
                    <div class="mb-6">
                        <label for="current_password" class="block text-sm font-medium text-secondary-700 mb-2">
                            Password Saat Ini <span class="text-danger-500">*</span>
                        </label>
                        <input type="password" name="current_password" id="current_password"
                               class="input w-full @error('current_password') border-danger-500 @enderror"
                               placeholder="Masukkan password saat ini"
                               required>
                        @error('current_password')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-secondary-700 mb-2">
                            Password Baru <span class="text-danger-500">*</span>
                        </label>
                        <input type="password" name="password" id="password"
                               class="input w-full @error('password') border-danger-500 @enderror"
                               placeholder="Masukkan password baru"
                               required>
                        @error('password')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-secondary-600">Minimal 8 karakter</p>
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-secondary-700 mb-2">
                            Konfirmasi Password <span class="text-danger-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="input w-full @error('password_confirmation') border-danger-500 @enderror"
                               placeholder="Ulangi password baru"
                               required>
                        @error('password_confirmation')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Password
                        </button>
                        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-ghost">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info Box --}}
        <div class="mt-6 bg-info-50 border border-info-200 rounded-lg p-4">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-info-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-info-700">
                    <p class="font-medium mb-1">Tips Keamanan:</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        <li>Gunakan kombinasi huruf besar, kecil, angka, dan simbol</li>
                        <li>Jangan gunakan password yang mudah ditebak</li>
                        <li>Jangan berbagi password dengan orang lain</li>
                        <li>Ubah password secara berkala untuk keamanan maksimal</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
