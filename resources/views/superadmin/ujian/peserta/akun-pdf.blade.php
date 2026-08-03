<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Akun Peserta</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #111; margin: 24px; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        p.meta { margin: 0 0 16px; color: #555; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; font-size: 11px; }
        td { font-size: 11px; }
        .no { width: 32px; text-align: center; }
    </style>
</head>
<body>
    <h1>Daftar Akun Peserta</h1>
    <p class="meta">
        Ujian: {{ $ujian->nama_ujian }}<br>
        @if($ujian->token_ujian) Token: {{ $ujian->token_ujian }}<br> @endif
        Total Peserta: {{ $peserta->count() }}
    </p>

    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Password</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peserta as $index => $item)
                <tr>
                    <td class="no">{{ $index + 1 }}</td>
                    <td>{{ $item->user?->name ?? '-' }}</td>
                    <td>{{ $item->user?->username ?? '-' }}</td>
                    <td>(sesuai yang dibuat admin)</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;">Belum ada peserta</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
