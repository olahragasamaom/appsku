@extends('superadmin.layouts.app')

@section('title', 'Tambah User')

@section('breadcrumb')
    <a href="{{ route('superadmin.users.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen User</a>
    <span class="text-secondary-900 font-medium">Tambah User</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Tambah User</h1>
            <p class="text-secondary-500 mt-1">Buat user baru untuk sistem</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('superadmin.users.store') }}" class="space-y-6 max-w-2xl">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-secondary-700 mb-2">Nama Lengkap *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="form-input w-full @error('name') border-danger-500 @enderror"
                           placeholder="Nama lengkap user">
                    @error('name')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-secondary-700 mb-2">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           class="form-input w-full @error('email') border-danger-500 @enderror"
                           placeholder="user@perusahaan.com">
                    @error('email')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-secondary-700 mb-2">Username *</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required
                           class="form-input w-full @error('username') border-danger-500 @enderror"
                           placeholder="username_user">
                    @error('username')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- User Level -->
                <div>
                    <label for="user_level_id" class="block text-sm font-medium text-secondary-700 mb-2">Level User</label>
                    <select id="user_level_id" name="user_level_id"
                            class="form-input w-full @error('user_level_id') border-danger-500 @enderror">
                        <option value="">-- Pilih Level User --</option>
                        @foreach($userLevels as $level)
                            <option value="{{ $level->id }}" {{ old('user_level_id') == $level->id ? 'selected' : '' }}>
                                {{ $level->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_level_id')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-secondary-700 mb-2">Password *</label>
                    <input type="password" id="password" name="password" required
                           class="form-input w-full @error('password') border-danger-500 @enderror"
                           placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-secondary-700 mb-2">Konfirmasi Password *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="form-input w-full @error('password_confirmation') border-danger-500 @enderror"
                           placeholder="Konfirmasi password">
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-4 h-4 text-primary-600 border-secondary-300 rounded focus:ring-primary-500">
                    <label for="is_active" class="text-sm font-medium text-secondary-700">Aktif</label>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-6 border-t border-secondary-200">
                    <button type="submit" class="btn btn-primary">
                        Simpan User
                    </button>
                    <a href="{{ route('superadmin.users.index') }}" class="btn btn-ghost">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
