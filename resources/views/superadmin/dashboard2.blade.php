@extends('superadmin.layouts.app')

@section('title', 'Dashboard IKU')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Dashboard IKU</span>
@endsection

@section('content')
<div x-data="{ tahun: '{{ $tahunAkademik[0] }}' }">

    {{-- HEADER: Judul + Filter Tahun Akademik --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-secondary-900">Capaian Indikator Kinerja Utama (IKU)</h1>
                <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-secondary-500 mt-1 max-w-3xl">
                Ringkasan performa pencapaian target strategis universitas berdasarkan Key Performance Indicator yang telah ditetapkan untuk tahun berjalan.
            </p>
        </div>

        {{-- Dropdown Tahun Akademik --}}
        <div class="relative w-full md:w-64 shrink-0">
            <div class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-secondary-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <select x-model="tahun" class="input w-full" style="padding-left: 36px;">
                @foreach($tahunAkademik as $th)
                    <option value="{{ $th }}">{{ $th }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- RINGKASAN CAPAIAN: 3 Gauge Chart --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        @foreach($ringkasan as $item)
            <div class="card">
                <div class="card-body p-6">
                    <h3 class="text-lg font-bold text-secondary-900">{{ $item['label'] }}</h3>
                    <p class="text-sm text-secondary-500 mt-1">{{ $item['deskripsi'] }}</p>

                    {{-- Gauge Chart (setengah lingkaran) menggunakan SVG --}}
                    @php
                        // Gauge setengah lingkaran (180 derajat).
                        // Keliling setengah lingkaran = PI * radius. radius = 80.
                        $radius = 80;
                        $circumference = pi() * $radius; // panjang busur setengah lingkaran
                        $persen = min($item['nilai'], 100);
                        $offset = $circumference - ($persen / 100 * $circumference);
                    @endphp
                    <div class="flex flex-col items-center mt-4">
                        <div class="relative" style="width: 200px; height: 110px;">
                            <svg width="200" height="110" viewBox="0 0 200 110">
                                {{-- Track abu-abu (background) --}}
                                <path d="M 20 100 A 80 80 0 0 1 180 100"
                                      fill="none"
                                      stroke="#e5e7eb"
                                      stroke-width="16"
                                      stroke-linecap="round" />
                                {{-- Progress berwarna --}}
                                <path d="M 20 100 A 80 80 0 0 1 180 100"
                                      fill="none"
                                      stroke="{{ $item['warna'] }}"
                                      stroke-width="16"
                                      stroke-linecap="round"
                                      stroke-dasharray="{{ $circumference }}"
                                      stroke-dashoffset="{{ $offset }}" />
                            </svg>
                            {{-- Angka persentase di tengah --}}
                            <div class="absolute inset-0 flex items-end justify-center pb-1">
                                <span class="text-4xl font-extrabold text-secondary-900">
                                    {{ number_format($item['nilai'], 1, ',', '.') }}%
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-secondary-500 mt-3">{{ $item['keterangan'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- CAPAIAN INDIKATOR TERENDAH --}}
    <div class="card mb-6">
        <div class="card-body p-6">
            <h3 class="text-lg font-bold text-secondary-900">Capaian Indikator Terendah</h3>
            <p class="text-sm text-secondary-500 mt-1 mb-4">Daftar indikator kinerja dengan nilai capaian terendah yang memerlukan perhatian khusus.</p>

            <div class="overflow-x-auto border border-secondary-200 rounded-lg">
                <table class="w-full">
                    <thead class="bg-secondary-50 border-b border-secondary-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-600 uppercase">Uraian</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-600 uppercase w-56">Capaian</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-secondary-600 uppercase w-32">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($indikatorTerendah as $row)
                            <tr class="border-b border-secondary-100 last:border-0">
                                <td class="px-4 py-4 text-sm text-secondary-800">{{ $row['uraian'] }}</td>
                                <td class="px-4 py-4">
                                    <x-iku-progress :value="$row['capaian']" :color="$row['warna']" />
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <x-iku-status :status="$row['status']" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- CAPAIAN JABATAN: Tertinggi & Terendah (2 kolom) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Jabatan Tertinggi --}}
        <div class="card">
            <div class="card-body p-6">
                <h3 class="text-lg font-bold text-secondary-900">Capaian Jabatan Tertinggi</h3>
                <p class="text-sm text-secondary-500 mt-1 mb-4">Daftar jabatan dengan nilai capaian kinerja tertinggi.</p>

                <div class="overflow-x-auto border border-secondary-200 rounded-lg">
                    <table class="w-full">
                        <thead class="bg-secondary-50 border-b border-secondary-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-600 uppercase">Jabatan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-600 uppercase w-40">Capaian</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-secondary-600 uppercase w-28">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jabatanTertinggi as $row)
                                <tr class="border-b border-secondary-100 last:border-0">
                                    <td class="px-4 py-4 text-sm text-secondary-800">{{ $row['jabatan'] }}</td>
                                    <td class="px-4 py-4">
                                        <x-iku-progress :value="$row['capaian']" :color="$row['warna']" />
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <x-iku-status :status="$row['status']" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Jabatan Terendah --}}
        <div class="card">
            <div class="card-body p-6">
                <h3 class="text-lg font-bold text-secondary-900">Capaian Jabatan Terendah</h3>
                <p class="text-sm text-secondary-500 mt-1 mb-4">Daftar jabatan dengan nilai capaian kinerja terendah.</p>

                <div class="overflow-x-auto border border-secondary-200 rounded-lg">
                    <table class="w-full">
                        <thead class="bg-secondary-50 border-b border-secondary-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-600 uppercase">Jabatan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-600 uppercase w-40">Capaian</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-secondary-600 uppercase w-28">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jabatanTerendah as $row)
                                <tr class="border-b border-secondary-100 last:border-0">
                                    <td class="px-4 py-4 text-sm text-secondary-800">{{ $row['jabatan'] }}</td>
                                    <td class="px-4 py-4">
                                        <x-iku-progress :value="$row['capaian']" :color="$row['warna']" />
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <x-iku-status :status="$row['status']" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- DAFTAR CAPAIAN IKU JABATAN (tabel lengkap) --}}
    <div class="card mb-6">
        <div class="card-body p-6">
            <h3 class="text-lg font-bold text-secondary-900">Daftar Capaian IKU Jabatan</h3>
            <p class="text-sm text-secondary-500 mt-1 mb-4">Rincian nilai capaian kinerja berdasarkan uraian indikator dan posisi jabatan.</p>

            <div class="overflow-x-auto border border-secondary-200 rounded-lg">
                <table class="w-full">
                    <thead class="bg-secondary-50 border-b border-secondary-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-600 uppercase">Uraian</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-secondary-600 uppercase w-28">Nilai Perkin</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-secondary-600 uppercase w-28">Nilai Kokin</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-secondary-600 uppercase w-28">Nilai Renstra</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-secondary-600 uppercase w-56">Rata-rata Capaian IKU</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-secondary-600 uppercase w-28">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ikuJabatan as $row)
                            <tr class="border-b border-secondary-100 last:border-0">
                                <td class="px-4 py-4 text-sm font-medium text-secondary-800">{{ $row['uraian'] }}</td>
                                <td class="px-4 py-4 text-center text-sm text-secondary-700">{{ $row['perkin'] }}%</td>
                                <td class="px-4 py-4 text-center text-sm text-secondary-700">{{ $row['kokin'] }}%</td>
                                <td class="px-4 py-4 text-center text-sm text-secondary-700">{{ $row['renstra'] }}%</td>
                                <td class="px-4 py-4">
                                    <x-iku-progress :value="$row['rata']" :color="$row['warna']" />
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <x-iku-status :status="$row['status']" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection