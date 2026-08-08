@extends('superadmin.layouts.app')

@section('title', 'Bank Soal')

@section('breadcrumb')
    <span class="text-secondary-900 font-medium">Bank Soal</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Bank Soal</h1>
            <p class="text-secondary-500 mt-1">Kelola butir soal dan pilihan jawaban</p>
        </div>
        <a href="{{ route('superadmin.soal.create', request()->only(['jenis_ujian_id', 'sub_jenis_ujian_id', 'sub_indikator_id'])) }}"
           class="btn btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Soal
        </a>
    </div>
@endsection

@section('content')
    <div class="card mb-6"
         x-data="{
            jenis_ujian_id: {{ Js::from(request('jenis_ujian_id', '')) }},
            sub_jenis_ujian_id: {{ Js::from(request('sub_jenis_ujian_id', '')) }},
            sub_indikator_id: {{ Js::from(request('sub_indikator_id', '')) }},
            subJenisOptions: [],
            subIndikatorOptions: [],
            async loadSubJenis(preserve = false) {
                if (!preserve) { this.sub_jenis_ujian_id = ''; this.sub_indikator_id = ''; }
                this.subJenisOptions = [];
                this.subIndikatorOptions = [];
                if (!this.jenis_ujian_id) return;
                const res = await fetch('{{ url('superadmin/soal/options/sub-jenis-ujian') }}/' + this.jenis_ujian_id);
                this.subJenisOptions = await res.json();
                if (preserve && this.sub_jenis_ujian_id) await this.loadSubIndikator(true);
            },
            async loadSubIndikator(preserve = false) {
                if (!preserve) this.sub_indikator_id = '';
                this.subIndikatorOptions = [];
                if (!this.sub_jenis_ujian_id) return;
                const res = await fetch('{{ url('superadmin/soal/options/sub-indikator') }}/' + this.sub_jenis_ujian_id);
                this.subIndikatorOptions = await res.json();
            },
            init() { if (this.jenis_ujian_id) this.loadSubJenis(true); }
         }">
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.soal.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Jenis Ujian</label>
                    <select name="jenis_ujian_id" x-model="jenis_ujian_id" @change="loadSubJenis()" class="input w-full text-sm">
                        <option value="">Semua</option>
                        @foreach($jenisUjians as $jenisUjian)
                            <option value="{{ $jenisUjian->id }}">{{ $jenisUjian->nama_jenis_ujian }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Sub Jenis Ujian</label>
                    <select name="sub_jenis_ujian_id" x-model="sub_jenis_ujian_id" @change="loadSubIndikator()" class="input w-full text-sm">
                        <option value="">Semua</option>
                        <template x-for="opt in subJenisOptions" :key="opt.id">
                            <option :value="opt.id" x-text="opt.nama_sub_jenis_ujian"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Sub Indikator</label>
                    <select name="sub_indikator_id" x-model="sub_indikator_id" class="input w-full text-sm">
                        <option value="">Semua</option>
                        <template x-for="opt in subIndikatorOptions" :key="opt.id">
                            <option :value="opt.id" x-text="opt.nama_sub_indikator"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Cari Soal</label>
                    <div class="flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" class="input w-full text-sm" placeholder="Kata kunci...">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body-sm">
            <x-table>
                <x-slot name="header">
                    <th class="px-6 py-3 text-left">Soal</th>
                    <th class="px-6 py-3 text-left">Sub Indikator</th>
                    <th class="px-6 py-3 text-left">Kunci / Poin</th>
                    <th class="px-6 py-3 text-left">Pembuat</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </x-slot>

                @forelse($soals as $soal)
                    <tr class="hover:bg-secondary-50">
                        <td class="px-6 py-4">
                            <p class="text-secondary-900 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($soal->soal), 90) }}</p>
                        </td>
                        <td class="px-6 py-4 text-secondary-700">
                            {{ $soal->subIndikator?->nama_sub_indikator ?? '-' }}
                            <span class="block text-xs text-secondary-400">{{ $soal->subIndikator?->subJenisUjian?->nama_sub_jenis_ujian }}</span>
                        </td>
                        <td class="px-6 py-4 text-secondary-700">
                            @if($soal->subIndikator?->subJenisUjian?->sistem_penilaian === 'benar_salah')
                                <span class="badge badge-primary">{{ $soal->kunci_jawaban }}</span>
                            @else
                                <span class="text-xs">Poin per jawaban</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-secondary-700">{{ $soal->pembuat?->name ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('superadmin.soal.preview', $soal) }}" target="_blank" class="btn btn-ghost btn-sm text-primary-600" title="Simulasi Tampilan Soal">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </a>
                                <a href="{{ route('superadmin.soal.edit', $soal) }}" class="btn btn-ghost btn-sm" title="Edit Soal">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button type="button"
                                        @click="$dispatch('confirm-dialog', {
                                            title: 'Hapus Soal',
                                            message: 'Apakah Anda yakin ingin menghapus soal ini?',
                                            confirmText: 'Ya, Hapus',
                                            type: 'danger',
                                            formAction: '{{ route('superadmin.soal.destroy', $soal) }}'
                                        })"
                                        class="btn btn-ghost btn-sm text-danger-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-secondary-500">
                            Belum ada soal yang dibuat
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        @if($soals->hasPages())
            <div class="card-footer">
                {{ $soals->links() }}
            </div>
        @endif
    </div>
@endsection
