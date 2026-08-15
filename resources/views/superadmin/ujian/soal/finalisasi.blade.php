@extends('superadmin.layouts.app')

@section('title', 'Finalisasi Soal Ujian')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.ujian.soal.index', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">Kelola Soal</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Finalisasi</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Finalisasi Soal — {{ $ujian->nama_ujian }}</h1>
            <p class="text-secondary-500 mt-1">Atur urutan soal per sub indikator, lalu finalisasi ujian</p>
        </div>
        <div class="flex items-center gap-2">
            @if($ujian->isFinalized())
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold bg-success-50 text-success-700 border border-success-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Final &middot; {{ $ujian->finalized_at->format('d M Y H:i') }}
                </span>
                <button type="button"
                        @click="$dispatch('confirm-dialog', {
                            title: 'Batalkan Finalisasi',
                            message: 'Kembalikan ujian ke status draft? Anda bisa mengubah dan mengurutkan soal lagi.',
                            confirmText: 'Ya, Batalkan',
                            type: 'warning',
                            formAction: '{{ route('superadmin.ujian.soal.unfinalize', $ujian) }}',
                            method: 'DELETE'
                        })"
                        class="btn btn-secondary">
                    Buka Kembali
                </button>
            @else
                <button type="button"
                        @click="$dispatch('confirm-dialog', {
                            title: 'Finalisasi Ujian',
                            message: 'Tandai ujian ini sebagai final? Susunan soal dianggap selesai disusun.',
                            confirmText: 'Ya, Finalisasi',
                            type: 'info',
                            formAction: '{{ route('superadmin.ujian.soal.finalize', $ujian) }}',
                            method: 'POST'
                        })"
                        class="btn btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Finalisasi Ujian
                </button>
            @endif
            <a href="{{ route('superadmin.ujian.soal.index', $ujian) }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
@endsection

@section('content')
    @if($jenisUjians->isEmpty())
        <div class="card">
            <div class="card-body text-center text-secondary-500">
                Ujian ini belum memiliki jenis ujian.
            </div>
        </div>
    @else
        {{-- Tab Navigasi Jenis Ujian --}}
        <div class="flex flex-wrap gap-2 mb-6 border-b border-secondary-200">
            @foreach($jenisUjians as $jenis)
                <a href="{{ route('superadmin.ujian.soal.finalisasi', ['ujian' => $ujian, 'jenis_ujian_id' => $jenis->id]) }}"
                   class="px-4 py-2 -mb-px text-sm font-medium border-b-2 transition-colors {{ $activeJenisId === $jenis->id ? 'border-primary-600 text-primary-600' : 'border-transparent text-secondary-500 hover:text-secondary-700' }}">
                    {{ $jenis->nama_jenis_ujian }}
                </a>
            @endforeach
        </div>

        <div class="card">
            <div class="card-header flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-secondary-900">Urutan Soal per Sub Indikator</h2>
                    <p class="text-sm text-secondary-500">Seret ikon di kiri soal untuk mengubah urutannya</p>
                </div>
                <span class="text-sm text-secondary-500">Total {{ $totalSoal }} soal</span>
            </div>
            <div class="card-body space-y-6">
                @forelse($ujianSoalsPerSubIndikator as $namaSubIndikator => $soalGroup)
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <h3 class="font-semibold text-secondary-800">{{ $namaSubIndikator }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-700">
                                {{ $soalGroup->count() }} soal
                            </span>
                        </div>
                        <div
                            x-data="soalReorder({ url: '{{ route('superadmin.ujian.soal.reorder', $ujian) }}' })"
                            x-init="init($el)"
                            class="space-y-2"
                            data-reorder-list>
                            @foreach($soalGroup as $index => $ujianSoal)
                                <div class="flex items-start gap-3 p-3 border border-secondary-100 rounded-lg bg-white hover:bg-secondary-50 transition-colors"
                                     data-id="{{ $ujianSoal->id }}">
                                    <button type="button"
                                            class="drag-handle flex-shrink-0 mt-0.5 cursor-grab active:cursor-grabbing text-secondary-300 hover:text-secondary-500 touch-none"
                                            title="Seret untuk mengurutkan">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7 4a1 1 0 100 2 1 1 0 000-2zM7 9a1 1 0 100 2 1 1 0 000-2zM7 14a1 1 0 100 2 1 1 0 000-2zM13 4a1 1 0 100 2 1 1 0 000-2zM13 9a1 1 0 100 2 1 1 0 000-2zM13 14a1 1 0 100 2 1 1 0 000-2z"/></svg>
                                    </button>
                                    <span class="flex-shrink-0 w-6 h-6 rounded-full bg-secondary-100 text-secondary-600 text-xs font-semibold flex items-center justify-center" data-nomor>{{ $index + 1 }}</span>
                                    <p class="flex-1 text-sm text-secondary-800 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($ujianSoal->soal->soal ?? ''), 120) }}</p>
                                    <a href="{{ route('superadmin.soal.preview', $ujianSoal->soal) }}" target="_blank" class="btn btn-ghost btn-sm flex-shrink-0 text-primary-600" title="Simulasi Tampilan Soal">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-center text-secondary-500 py-8">Belum ada soal pada kategori ini</p>
                @endforelse
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    function soalReorder(config) {
        return {
            url: config.url,
            saving: false,
            init(el) {
                if (!window.Sortable) return;
                window.Sortable.create(el, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    onEnd: () => this.persist(el),
                });
            },
            renumber(el) {
                el.querySelectorAll('[data-nomor]').forEach((node, i) => {
                    node.textContent = i + 1;
                });
            },
            async persist(el) {
                this.renumber(el);
                const ids = [...el.querySelectorAll('[data-id]')].map(n => parseInt(n.dataset.id));
                this.saving = true;
                try {
                    await fetch(this.url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ ujian_soal_ids: ids }),
                    });
                } finally {
                    this.saving = false;
                }
            }
        };
    }
</script>
@endpush
