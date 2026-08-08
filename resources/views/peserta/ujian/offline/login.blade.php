<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Login Ujian - {{ $ujian->nama_ujian }}</title>
</head>
<body>
    <h1>Login Ujian: {{ $ujian->nama_ujian }}</h1>

    @if($errors->any())
        <div style="color: red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('peserta.ujian.offline.login', $ujian) }}" method="POST">
        @csrf
        <div>
            <label>Nomor Peserta</label>
            <input type="text" name="nomor_peserta" value="{{ old('nomor_peserta') }}" required>
        </div>
        <div>
            <label>Kode Akses</label>
            <input type="password" name="kode_akses" required>
        </div>
        <button type="submit">Mulai Ujian</button>
    </form>
</body>
</html>
