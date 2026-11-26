@extends('pengawas.layouts.app')

@section('title', 'Aktivasi Peserta - ' . $ujian->nama)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('pengawas.absensi.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium mb-2 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Absensi
        </a>
        <h1 class="text-3xl font-bold text-secondary-900">Aktivasi Peserta</h1>
        <div class="flex items-center gap-3 mt-2">
            <p class="text-secondary-500">{{ $ujian->nama_ujian ?? $ujian->nama }}</p>
            @php $token = $ujian->token_ujian ?? null; @endphp
            @if($token)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-mono font-bold bg-amber-100 text-amber-900 border border-amber-300 shadow-xs" title="Token Ujian">
                    <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Token: {{ $token }}
                </span>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($peserta->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-secondary-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-secondary-900 mb-2">Belum ada peserta untuk ujian ini</h3>
                </div>
            @else
                <form method="POST" action="{{ route('pengawas.absensi.bulk-toggle', $ujian) }}" class="mb-6">
                    @csrf
                    <div class="flex gap-3 items-center">
                        <select name="action" class="form-input flex-1" required>
                            <option value="">-- Pilih Aksi --</option>
                            <option value="activate">Aktifkan Semua</option>
                            <option value="deactivate">Nonaktifkan Semua</option>
                        </select>
                        <button type="submit" class="btn btn-secondary" onclick="return confirm('Yakin dengan aksi ini?')">
                            Terapkan
                        </button>
                    </div>
                    @error('action')
                        <p class="mt-1 text-sm text-danger-500">{{ $message }}</p>
                    @enderror
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary-200">
                        <thead class="bg-secondary-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-700 uppercase">Nomor Peserta</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-700 uppercase">Nama</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-secondary-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-secondary-700 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-secondary-200">
                            @foreach($peserta as $p)
                                <tr class="hover:bg-secondary-50">
                                    <td class="px-6 py-4 font-medium text-secondary-900">{{ $p->nomor_peserta }}</td>
                                    <td class="px-6 py-4 text-secondary-900">{{ $p->nama_peserta }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if($p->is_active)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-success-100 text-success-700">
                                                AKTIF
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-secondary-100 text-secondary-700">
                                                NONAKTIF
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <form method="POST" action="{{ route('pengawas.absensi.toggle', [$ujian, $p]) }}" style="display: inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-primary-600 hover:text-primary-700 text-sm font-medium"
                                                    onclick="return confirm('Yakin mengubah status peserta ini?')">
                                                {{ $p->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $peserta->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
