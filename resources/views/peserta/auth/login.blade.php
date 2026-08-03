<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Peserta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-100 to-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white text-2xl font-bold mx-auto mb-3">U</div>
            <h1 class="text-2xl font-bold text-slate-800">Portal Peserta Ujian</h1>
            <p class="text-slate-500 text-sm mt-1">Masuk menggunakan username dan password</p>
        </div>

        <div class="card">
            <div class="card-body">
                @if($errors->any())
                    <x-alert type="danger" class="mb-4">{{ $errors->first() }}</x-alert>
                @endif

                <form method="POST" action="{{ route('peserta.login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                        <input type="text" name="username" id="username" value="{{ old('username') }}"
                               class="input w-full" required autofocus>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input type="password" name="password" id="password" class="input w-full" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
