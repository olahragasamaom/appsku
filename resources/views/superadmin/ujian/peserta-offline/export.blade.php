<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Peserta - {{ $ujian->nama_ujian }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .subtitle { font-size: 12px; color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
        th { background-color: #f1f5f9; }
        .note { margin-top: 16px; font-size: 11px; color: #64748b; }
    </style>
</head>
<body>
    <h1>Kartu Peserta Ujian</h1>
    <div class="subtitle">{{ $ujian->nama_ujian }} &mdash; Token: {{ $ujian->token_ujian ?? '-' }}</div>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nomor Peserta</th>
                <th>Nama Peserta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peserta as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->nomor_peserta }}</td>
                    <td>{{ $item->nama_peserta }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="note">
        Kode akses dibagikan secara terpisah saat peserta didaftarkan dan tidak ditampilkan kembali di kartu ini demi keamanan.
    </p>
</body>
</html>
