<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pengajar Login - Panritta</title>
    <meta name="description" content="Masuk ke portal Pengajar Panritta.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; position: relative;">
        {{-- Background Decorations --}}
        <div style="position: absolute; inset: 0; overflow: hidden; pointer-events: none;">
            <div style="position: absolute; top: -96px; left: -96px; width: 384px; height: 384px; background: rgba(245, 158, 11, 0.1); border-radius: 50%; filter: blur(64px);"></div>
            <div style="position: absolute; bottom: -96px; right: -96px; width: 384px; height: 384px; background: rgba(249, 115, 22, 0.1); border-radius: 50%; filter: blur(64px);"></div>
        </div>

        <div style="position: relative; z-index: 10; width: 100%; max-width: 448px;">
            {{-- Logo --}}
            <div style="text-align: center; margin-bottom: 32px;">
                <a href="{{ url('/') }}" style="display: inline-flex; align-items: center; gap: 12px; text-decoration: none;">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); border-radius: 16px; display: flex; align-items: center; justify-content: center;">
                        <svg style="width: 32px; height: 32px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <span style="font-size: 24px; font-weight: 700; color: white;">Pengajar</span>
                </a>
            </div>

            {{-- Login Card --}}
            <div style="background: white; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); padding: 32px;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <h2 style="font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">Login Pengajar</h2>
                    <p style="color: #64748b;">Masuk ke portal pembuatan soal</p>
                </div>

                <form method="POST" action="{{ route('pengajar.login') }}">
                    @csrf

                    {{-- Username --}}
                    <div style="margin-bottom: 20px;">
                        <label for="username" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                               class="input w-full @error('username') border-danger-500 @enderror"
                               placeholder="Masukkan username Anda">
                        @error('username')
                            <p style="margin-top: 4px; font-size: 14px; color: #ef4444;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div x-data="{ show: false }" style="margin-bottom: 24px;">
                        <label for="password" style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 8px;">Password</label>
                        <div style="position: relative;">
                            <input :type="show ? 'text' : 'password'" id="password" name="password" required
                                   class="input w-full @error('password') border-danger-500 @enderror"
                                   style="padding-right: 48px;"
                                   placeholder="Masukkan password">
                            <button type="button" @click="show = !show"
                                    style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #9ca3af;"
                                    onmouseover="this.style.color='#4b5563'" onmouseout="this.style.color='#9ca3af'">
                                <svg x-show="!show" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" x-cloak style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p style="margin-top: 4px; font-size: 14px; color: #ef4444;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <button type="submit" style="width: 100%; padding: 14px 24px; background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); color: white; font-weight: 600; border-radius: 12px; border: none; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.25); transition: all 0.2s;"
                            onmouseover="this.style.background='linear-gradient(135deg, #d97706 0%, #c2410c 100%)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #f59e0b 0%, #ea580c 100%)'">
                        <span style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Masuk
                        </span>
                    </button>
                </form>

                {{-- Back to main site --}}
                <div style="margin-top: 24px; text-align: center;">
                    <a href="{{ url('/') }}" style="font-size: 14px; color: #64748b; text-decoration: none;"
                       onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#64748b'">
                        &larr; Kembali ke halaman utama
                    </a>
                </div>
            </div>

            {{-- Security Note --}}
            <div style="margin-top: 24px; display: flex; align-items: center; justify-content: center; gap: 8px; color: #94a3b8; font-size: 14px;">
                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Akses terbatas untuk pengajar</span>
            </div>
        </div>
    </div>
</body>
</html>
