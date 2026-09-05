@extends('superadmin.layouts.app')

@section('title', 'Assign Peserta')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.ujian.peserta-offline.index', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">Peserta Offline</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Assign Peserta</span>
@endsection

@section('header')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-semibold text-xl text-secondary-800">Assign Peserta ke Ujian</h2>
            <p class="text-sm text-secondary-500">{{ $ujian->nama_ujian }}</p>
        </div>
        <a href="{{ route('superadmin.ujian.peserta-offline.index', $ujian) }}" class="btn btn-ghost">Kembali</a>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        @if(session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger">{{ session('error') }}</x-alert>
        @endif

        <!-- Copy dari Ujian Lain -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Copy Peserta dari Ujian Lain</h3>
                <p class="text-sm text-secondary-500 mt-1">
                    Salin semua peserta dari ujian lain ke ujian ini (duplikat otomatis di-skip).
                </p>
            </div>
            <div class="card-body">
                @if($sourceUjianList->isEmpty())
                    <p class="text-sm text-secondary-500 italic">Tidak ada ujian offline lain yang tersedia.</p>
                @else
                    <form action="{{ route('superadmin.ujian.peserta-offline.assign.copy', $ujian) }}" method="POST"
                          class="flex gap-4 items-end">
                        @csrf
                        <div class="flex-1">
                            <label for="source_ujian_id" class="block text-sm font-medium text-secondary-700 mb-1">
                                Pilih Ujian Sumber
                            </label>
                            <select name="source_ujian_id" id="source_ujian_id" class="input w-full" required>
                                <option value="">-- Pilih Ujian --</option>
                                @foreach($sourceUjianList as $source)
                                    <option value="{{ $source->id }}">
                                        {{ $source->nama_ujian }} ({{ $source->peserta_offline_count }} peserta)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary"
                                onclick="return confirm('Yakin ingin copy semua peserta dari ujian ini?')">
                            Copy Peserta
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Peserta yang Sudah Assigned -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Peserta yang Sudah Assigned ({{ $assignedPeserta->count() }})</h3>
                <p class="text-sm text-secondary-500 mt-1">
                    Peserta ini akan bisa mengikuti ujian '<strong>{{ $ujian->nama_ujian }}</strong>'.
                </p>
            </div>
            <div class="card-body p-0">
                @if($assignedPeserta->isEmpty())
                    <div class="text-center py-8 text-secondary-500">
                        <p>Belum ada peserta yang di-assign.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-secondary-200 bg-secondary-50">
                                    <th class="text-left py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Nomor Peserta</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Nama Peserta</th>
                                    <th class="text-left py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Asal Ujian</th>
                                    <th class="text-center py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignedPeserta as $peserta)
                                    <tr class="border-b border-secondary-100 hover:bg-secondary-50">
                                        <td class="py-3 px-4 text-sm font-medium text-secondary-800">{{ $peserta->nomor_peserta }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-700">{{ $peserta->nama_peserta }}</td>
                                        <td class="py-3 px-4 text-sm text-secondary-500">
                                            {{ $peserta->ujian->nama_ujian ?? '-' }}
                                            @if($peserta->ujian_id === $ujian->id)
                                                <span class="ml-1 text-xs bg-primary-100 text-primary-700 px-2 py-0.5 rounded">Native</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($peserta->ujian_id !== $ujian->id)
                                                <form action="{{ route('superadmin.ujian.peserta-offline.assign.unassign', [$ujian, $peserta]) }}"
                                                      method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            onclick="return confirm('Yakin ingin lepas peserta ini dari ujian?')"
                                                            class="btn btn-ghost btn-sm text-danger-600">
                                                        Lepas
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-secondary-400 italic">Tidak bisa dilepas (native)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Peserta yang Tersedia untuk di-Assign -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Peserta Tersedia untuk di-Assign ({{ $availablePeserta->count() }})</h3>
                <p class="text-sm text-secondary-500 mt-1">
                    Pilih peserta dari ujian lain untuk juga bisa ikut ujian ini.
                </p>
            </div>
            <div class="card-body p-0">
                @if($availablePeserta->isEmpty())
                    <div class="text-center py-8 text-secondary-500">
                        <p>Semua peserta sudah di-assign atau belum ada peserta di ujian lain.</p>
                    </div>
                @else
                    <form action="{{ route('superadmin.ujian.peserta-offline.assign.store', $ujian) }}" method="POST">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-secondary-200 bg-secondary-50">
                                        <th class="text-left py-3 px-4 w-10">
                                            <input type="checkbox" id="selectAllAvailable" class="w-4 h-4">
                                        </th>
                                        <th class="text-left py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Nomor Peserta</th>
                                        <th class="text-left py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Nama Peserta</th>
                                        <th class="text-left py-3 px-4 text-xs font-medium text-secondary-700 uppercase">Ujian Asal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($availablePeserta as $peserta)
                                        <tr class="border-b border-secondary-100 hover:bg-secondary-50">
                                            <td class="py-3 px-4">
                                                <input type="checkbox" name="peserta_ids[]" value="{{ $peserta->id }}"
                                                       class="w-4 h-4 availableCheckbox">
                                            </td>
                                            <td class="py-3 px-4 text-sm font-medium text-secondary-800">{{ $peserta->nomor_peserta }}</td>
                                            <td class="py-3 px-4 text-sm text-secondary-700">{{ $peserta->nama_peserta }}</td>
                                            <td class="py-3 px-4 text-sm text-secondary-500">{{ $peserta->ujian->nama_ujian ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-secondary-200 flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                Assign Peserta Terpilih
                            </button>
                        </div>
                    </form>

                    <script>
                        (function () {
                            const selectAll = document.getElementById('selectAllAvailable');
                            const checkboxes = document.querySelectorAll('.availableCheckbox');
                            selectAll?.addEventListener('change', function () {
                                checkboxes.forEach(cb => cb.checked = this.checked);
                            });
                        })();
                    </script>
                @endif
            </div>
        </div>
    </div>
@endsection
