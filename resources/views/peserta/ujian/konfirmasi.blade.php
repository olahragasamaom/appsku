@extends('peserta.layouts.app')

@section('title', 'Mulai Ujian')

@section('content')
    <div class="max-w-lg mx-auto">
        <a href="{{ route('peserta.dashboard') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Kembali ke Dashboard</a>

        <div class="card mt-4">
            <div class="card-body">
                <h1 class="text-xl font-bold text-slate-800">{{ $ujian->nama_ujian }}</h1>
                <div class="mt-3 space-y-1 text-sm text-slate-600">
                    <p>Jumlah Soal: <strong>{{ $ujian->jumlah_soal }}</strong></p>
                    @if($ujian->durasi_ujian)
                        <p>Durasi: <strong>{{ $ujian->durasi_ujian }} menit</strong></p>
                    @endif
                    @if($ujian->tanggal_ujian)
                        <p>Jadwal: <strong>{{ $ujian->tanggal_ujian->format('d M Y H:i') }}</strong></p>
                    @endif
                </div>

                @if($errors->any())
                    <x-alert type="danger" class="mt-4">{{ $errors->first() }}</x-alert>
                @endif

                <form method="POST" action="{{ route('peserta.ujian.start', $ujian) }}" class="mt-6 space-y-4">
                    @csrf
                    @if($ujian->isOffline())
                        <div>
                            <label for="token" class="block text-sm font-medium text-slate-700 mb-1">Token Ujian</label>
                            <input type="text" name="token" id="token" class="input w-full uppercase"
                                   placeholder="Masukkan token dari pengawas" required autofocus>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-primary w-full">
                        {{ ($peserta && $peserta->status === 'sedang_ujian') ? 'Lanjutkan Ujian' : 'Mulai Ujian' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
