@extends('pengawas.layouts.app')

@section('title', 'Ganti Password')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-secondary-900">Ganti Password</h1>
        <p class="text-secondary-500 mt-2">Ubah password akun Anda</p>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="PUT" action="{{ route('pengawas.change-password.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-secondary-700 mb-2">
                        Password Saat Ini
                    </label>
                    <input type="password" id="current_password" name="current_password" required
                           class="form-input w-full @error('current_password') border-danger-500 @enderror"
                           placeholder="Masukkan password saat ini">
                    @error('current_password')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-secondary-700 mb-2">
                        Password Baru
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
                        Konfirmasi Password Baru
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="form-input w-full @error('password_confirmation') border-danger-500 @enderror"
                           placeholder="Konfirmasi password baru">
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-6 border-t border-secondary-200">
                    <button type="submit" class="btn btn-primary">
                        Simpan Password
                    </button>
                    <a href="{{ route('pengawas.dashboard') }}" class="btn btn-ghost">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
