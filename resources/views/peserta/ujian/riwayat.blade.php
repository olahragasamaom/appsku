@extends('peserta.layouts.app')

@section('title', 'Riwayat Ujian')

@section('content')
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('peserta.offline.exams') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Kembali ke Dashboard</a>

        <div class="card mt-4">
            <div class="card-body">
                <h1 class="text-2xl font-bold text-slate-800 mb-1">{{ $ujian->nama_ujian }}</h1>
                <p class="text-sm text-slate-500">Riwayat Semua Percobaan Ujian</p>
            </div>
        </div>

        @if(count($attempts) > 0)
            <div class="card mt-6">
                <div class="card-body">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200">
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Percobaan</th>
                                    <th class="text-left py-3 px-4 font-semibold text-slate-700">Tanggal Mulai</th>
                                    <th class="text-center py-3 px-4 font-semibold text-slate-700">Nilai Akhir</th>
                                    <th class="text-center py-3 px-4 font-semibold text-slate-700">Status</th>
                                    <th class="text-center py-3 px-4 font-semibold text-slate-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attempts as $index => $attempt)
                                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-600 font-semibold text-sm">
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-600">
                                            {{ $attempt->waktu_mulai?->format('d M Y H:i') ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="text-lg font-bold {{ $attempt->total_nilai >= 60 ? 'text-success-600' : 'text-danger-600' }}">
                                                {{ $attempt->total_nilai ?? 0 }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($attempt->status === 'selesai')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800">
                                                    Selesai
                                                </span>
                                            @elseif($attempt->status === 'sedang_ujian')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Sedang Ujian
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                                    {{ ucfirst($attempt->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($attempt->status === 'selesai')
                                                <a href="{{ route('peserta.ujian.pembahasan', [$ujian, 'attempt' => $attempt->id]) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
                                                    Lihat
                                                </a>
                                            @else
                                                <span class="text-slate-400 text-sm">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if(count($attempts) > 1)
                <div class="card mt-6">
                    <div class="card-body">
                        <h2 class="text-lg font-bold text-slate-800 mb-4">Grafik Perkembangan Nilai</h2>
                        <div style="position: relative; height: 300px;">
                            <canvas id="progressChart"></canvas>
                        </div>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    const attempts = @json($attempts->map(fn($a) => [
                        'attempt' => 'Percobaan ' . (collect($attempts)->search($a) + 1),
                        'nilai' => $a->total_nilai ?? 0,
                    ])->reverse());

                    const ctx = document.getElementById('progressChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: attempts.map(a => a.attempt),
                            datasets: [{
                                label: 'Nilai Ujian',
                                data: attempts.map(a => a.nilai),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#3b82f6',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    labels: { font: { size: 12 } }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: { stepSize: 10 }
                                }
                            }
                        }
                    });
                </script>
            @endif
        @else
            <div class="card mt-6">
                <div class="card-body text-center py-12">
                    <p class="text-slate-600">Belum ada riwayat ujian.</p>
                </div>
            </div>
        @endif
    </div>
@endsection
