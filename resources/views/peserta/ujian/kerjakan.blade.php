@extends('peserta.layouts.app')

@section('title', 'Mengerjakan Ujian')

@section('content')
<div x-data="examEngine({
        saveUrl: '{{ route('peserta.ujian.jawaban', $ujian) }}',
        sisaDetik: {{ $sisaDetik === null ? 'null' : $sisaDetik }},
        submitFormId: 'submit-form'
     })"
     x-init="init()">

    {{-- Header sticky: timer + submit --}}
    <div class="flex items-center justify-between bg-white border border-slate-200 rounded-xl px-4 py-3 mb-6 sticky top-16 z-20">
        <div>
            <h1 class="font-semibold text-slate-800">{{ $ujian->nama_ujian }}</h1>
            <p class="text-xs text-slate-500" x-show="saving" x-cloak>Menyimpan...</p>
            <p class="text-xs text-success-600" x-show="!saving && lastSaved" x-cloak>Tersimpan</p>
        </div>
        <div class="flex items-center gap-4">
            <template x-if="sisaDetik !== null">
                <div class="text-right">
                    <span class="block text-xs text-slate-400 uppercase">Sisa Waktu</span>
                    <span class="text-lg font-bold" :class="sisaDetik <= 60 ? 'text-danger-600' : 'text-slate-800'" x-text="formatTime(sisaDetik)"></span>
                </div>
            </template>
            <button type="button" @click="confirmSubmit()" class="btn btn-primary btn-sm">Selesai</button>
        </div>
    </div>

    <div class="space-y-6">
        @foreach($ujianSoals as $index => $ujianSoal)
            @php $soal = $ujianSoal->soal; @endphp
            <div class="card">
                <div class="card-body">
                    <div class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 text-primary-700 font-semibold flex items-center justify-center text-sm">{{ $index + 1 }}</span>
                        <div class="flex-1">
                            <div class="prose prose-sm max-w-none text-slate-800">{!! $soal->soal !!}</div>
                            @if($soal->gambar_soal)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($soal->gambar_soal) }}" alt="Gambar soal" class="mt-3 max-h-64 rounded-lg">
                            @endif

                            <div class="mt-4 space-y-2">
                                @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
                                    @php $opsiText = $soal->{'opsi_'.strtolower($opsi)}; @endphp
                                    @if($opsiText !== null && $opsiText !== '')
                                        <label class="flex items-start gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                            <input type="radio"
                                                   name="soal_{{ $ujianSoal->id }}"
                                                   value="{{ $opsi }}"
                                                   @checked(($jawaban[$ujianSoal->id] ?? null) === $opsi)
                                                   @change="save({{ $ujianSoal->id }}, '{{ $opsi }}')"
                                                   class="mt-1 text-primary-600">
                                            <span class="text-sm text-slate-700"><strong>{{ $opsi }}.</strong> {{ $opsiText }}</span>
                                        </label>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex justify-end mt-8">
        <button type="button" @click="confirmSubmit()" class="btn btn-primary">Selesaikan Ujian</button>
    </div>

    <form id="submit-form" method="POST" action="{{ route('peserta.ujian.submit', $ujian) }}" class="hidden">
        @csrf
    </form>
</div>
@endsection

@push('scripts')
<script>
    function examEngine(config) {
        return {
            saveUrl: config.saveUrl,
            sisaDetik: config.sisaDetik,
            submitFormId: config.submitFormId,
            saving: false,
            lastSaved: false,
            timer: null,

            init() {
                if (this.sisaDetik !== null) {
                    this.timer = setInterval(() => {
                        this.sisaDetik--;
                        if (this.sisaDetik <= 0) {
                            clearInterval(this.timer);
                            this.doSubmit();
                        }
                    }, 1000);
                }
            },

            async save(ujianSoalId, jawaban) {
                this.saving = true;
                this.lastSaved = false;
                try {
                    await fetch(this.saveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ ujian_soal_id: ujianSoalId, jawaban: jawaban }),
                    });
                    this.lastSaved = true;
                } finally {
                    this.saving = false;
                }
            },

            confirmSubmit() {
                if (confirm('Selesaikan dan kirim ujian? Jawaban tidak dapat diubah setelah ini.')) {
                    this.doSubmit();
                }
            },

            doSubmit() {
                document.getElementById(this.submitFormId).submit();
            },

            formatTime(seconds) {
                const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                const s = (seconds % 60).toString().padStart(2, '0');
                return `${m}:${s}`;
            }
        };
    }
</script>
@endpush
