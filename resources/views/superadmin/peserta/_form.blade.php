@php $peserta = $peserta ?? null; @endphp

<div class="card">
    <div class="card-body space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-secondary-700 mb-1">
                Nama Lengkap <span class="text-danger-500">*</span>
            </label>
            <input type="text" name="name" id="name" value="{{ old('name', $peserta->name ?? '') }}"
                   class="input w-full @error('name') border-danger-500 @enderror" required>
            @error('name')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="username" class="block text-sm font-medium text-secondary-700 mb-1">
                    Username <span class="text-danger-500">*</span>
                </label>
                <input type="text" name="username" id="username" value="{{ old('username', $peserta->username ?? '') }}"
                       class="input w-full @error('username') border-danger-500 @enderror" required>
                @error('username')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-secondary-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $peserta->email ?? '') }}"
                       class="input w-full @error('email') border-danger-500 @enderror" placeholder="Opsional">
                @error('email')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="phone" class="block text-sm font-medium text-secondary-700 mb-1">No. HP</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $peserta->phone ?? '') }}"
                       class="input w-full" placeholder="Opsional">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-secondary-700 mb-1">
                    Password @if(!$peserta)<span class="text-danger-500">*</span>@endif
                </label>
                <input type="text" name="password" id="password"
                       class="input w-full @error('password') border-danger-500 @enderror"
                       placeholder="{{ $peserta ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' }}">
                @error('password')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded border-secondary-300 text-primary-600"
                   @checked(old('is_active', $peserta->is_active ?? true))>
            <span class="text-sm text-secondary-700">Akun Aktif</span>
        </label>
    </div>
</div>

<div class="flex items-center justify-end gap-3 mt-6">
    <a href="{{ route('superadmin.peserta.index') }}" class="btn btn-secondary">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Simpan' }}</button>
</div>
