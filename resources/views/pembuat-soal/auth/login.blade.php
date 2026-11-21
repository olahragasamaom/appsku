<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pembuat Soal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>


<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Pembuat Soal</h1>
                <p class="text-gray-600 text-sm mt-2">Masuk ke portal manajemen ujian & soal</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pembuat-soal.login') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="pembuat"
                        @error('username') aria-invalid="true" @enderror>
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="••••••••"
                        @error('password') aria-invalid="true" @enderror>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition font-medium">
                    Masuk
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-200 text-center text-sm text-gray-600">
                <p>Kembali ke <a href="{{ route('home') }}" class="text-blue-600 hover:underline">halaman utama</a></p>
            </div>
        </div>

        <div class="mt-6 p-4 bg-white rounded-lg shadow text-center text-sm text-gray-600">
            <p class="mb-2 font-medium">Demo Credentials:</p>
            <p>Username: <code class="bg-gray-100 px-2 py-1 rounded">pembuat</code></p>
            <p>Password: <code class="bg-gray-100 px-2 py-1 rounded">password123</code></p>
        </div>
    </div>
</div>
</body>
</html>
