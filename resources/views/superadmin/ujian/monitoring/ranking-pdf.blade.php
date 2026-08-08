<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Perankingan Peserta</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #111; margin: 24px; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        p.meta { margin: 0 0 16px; color: #555; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
        th { background: #f0f0f0; font-size: 11px; }
        td { font-size: 11px; }
        .rank { width: 40px; text-align: center; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>Perankingan Peserta</h1>
    <p class="meta">
        Ujian: {{ $ujian->nama_ujian }}<br>
        Diurutkan berdasarkan nilai kumulatif tertinggi<br>
        Total Peserta: {{ $ranking->count() }}
    </p>

    <table>
        <thead>
            <tr>
                <th class="rank">Rank</th>
                <th>Nama</th>
                <th>Username</th>
                <th class="center">Total Nilai</th>
                <th class="center">Kelulusan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ranking as $index => $item)
                <tr>
                    <td class="rank">{{ $index + 1 }}</td>
                    <td>{{ $item->user?->name ?? '-' }}</td>
                    <td>{{ $item->user?->username ?? '-' }}</td>
                    <td class="center">{{ $item->total_nilai ?? '-' }}</td>
                    <td class="center">
                        @if($item->lulus === true)
                            Lulus
                        @elseif($item->lulus === false)
                            Tidak Lulus
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center">Belum ada peserta</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
