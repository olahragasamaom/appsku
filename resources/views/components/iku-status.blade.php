@props(['status' => 'kuning'])

@php
    // Konfigurasi tampilan tiap status: label, warna teks, warna background,
    // warna border, dan ikon SVG.
    $config = [
        'emas' => [
            'label' => 'Emas',
            'classes' => 'text-warning-600 bg-warning-50 border-warning-500/30',
            'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        ],
        'hijau' => [
            'label' => 'Hijau',
            'classes' => 'text-success-600 bg-success-50 border-success-500/30',
            'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'kuning' => [
            'label' => 'Kuning',
            'classes' => 'text-warning-600 bg-warning-50 border-warning-500/30',
            'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'merah' => [
            'label' => 'Merah',
            'classes' => 'text-danger-600 bg-danger-50 border-danger-500/30',
            'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        ],
    ];

    $item = $config[$status] ?? $config['kuning'];
@endphp

<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full border text-xs font-semibold {{ $item['classes'] }}">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
    </svg>
    {{ $item['label'] }}
</span>
