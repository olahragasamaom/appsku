@props(['value' => 0, 'color' => 'kuning'])

@php
    // Mapping nama warna ke kode hex untuk bar progress.
    $colorMap = [
        'hijau' => '#14b8a6', // success-500 (teal)
        'kuning' => '#f59e0b', // warning-500 (amber)
        'merah' => '#f43f5e', // danger-500 (rose)
    ];
    $barColor = $colorMap[$color] ?? '#f59e0b';

    // Batasi lebar bar maksimal 100% meskipun nilai bisa > 100 (mis. 120%).
    $barWidth = min($value, 100);
@endphp

{{-- Progress bar horizontal + angka persentase di sebelah kanan --}}
<div class="flex items-center gap-3">
    <div class="flex-1 h-2 rounded-full bg-secondary-200 overflow-hidden">
        <div class="h-full rounded-full"
             style="width: {{ $barWidth }}%; background-color: {{ $barColor }};"></div>
    </div>
    <span class="text-sm font-semibold text-secondary-700 w-12 text-right">{{ $value }}%</span>
</div>
