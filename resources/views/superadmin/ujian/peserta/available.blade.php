@extends('superadmin.layouts.app')

@section('title', 'Tambah Peserta Ujian')

@section('breadcrumb')
    <a href="{{ route('superadmin.ujian.index') }}" class="text-secondary-500 hover:text-secondary-700">Manajemen Ujian</a>
    <span class="mx-2 text-secondary-400">/</span>
    <a href="{{ route('superadmin.ujian.peserta.index', $ujian) }}" class="text-secondary-500 hover:text-secondary-700">Peserta</a>
    <span class="mx-2 text-secondary-400">/</span>
    <span class="text-secondary-900 font-medium">Tambah</span>
@endsection

@section('header')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-secondary-900">Tambah Peserta — {{ $ujian->nama_ujian }}</h1>
            <p class="text-secondary-500 mt-1">Centang peserta dari master data untuk ditambahkan</p>
        </div>
        <a href="{{ route('superadmin.ujian.peserta.index', $ujian) }}" class="btn btn-secondary">Kembali</a>
    </div>
@endsection

@section('content')
    <div class="card mb-6">
        <div class="card-body-sm">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="input flex-1" placeholder="Cari nama / username...">
                <button type="submit" class="btn btn-secondary">Cari</button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('superadmin.ujian.peserta.store', $ujian) }}">
        @csrf
        <div class="card">
            <div class="card-body-sm">
                <x-table>
                    <x-slot name="header">
                        <th class="px-6 py-3 text-left w-12"></th>
                        <th class="px-6 py-3 text-left">Nama</th>
                        <th class="px-6 py-3 text-left">Username</th>
                        <th class="px-6 py-3 text-left">Email</th>
                    </x-slot>

                    @forelse($available as $user)
                        <tr class="hover:bg-secondary-50">
                            <td class="px-6 py-4">
                                <input type="checkbox" name="user_id[]" value="{{ $user->id }}" class="rounded border-secondary-300 text-primary-600">
                            </td>
                            <td class="px-6 py-4 font-medium text-secondary-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-secondary-700">{{ $user->username }}</td>
                            <td class="px-6 py-4 text-secondary-500 text-sm">{{ $user->email }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-secondary-500">
                                Semua peserta sudah dialokasikan atau belum ada peserta di master data
                            </td>
                        </tr>
                    @endforelse
                </x-table>
            </div>

            @if($available->hasPages())
                <div class="card-footer">
                    {{ $available->links() }}
                </div>
            @endif
        </div>

        @if($available->count() > 0)
            <div class="flex justify-end mt-6">
                <button type="submit" class="btn btn-primary">Tambahkan Peserta Terpilih</button>
            </div>
        @endif
    </form>
@endsection
