@php
    $soal = $soal ?? null;
    // $locked opsional: default false bila tidak dikirim controller (mis. saat edit)
    $locked = $locked ?? false;
    $currentSubIndikator = $soal?->subIndikator ?? ($subIndikator ?? null);
    $currentSubJenis = $currentSubIndikator?->subJenisUjian;
    $currentJenisUjian = $currentSubJenis?->jenisUjian ?? $currentSubIndikator?->jenisUjian;
@endphp

<div
    x-data="{
        jenis_ujian_id: String({{ Js::from(old('jenis_ujian_id', $currentJenisUjian?->id ?? '')) }}),
        sub_jenis_ujian_id: '',
        sub_indikator_id: '',
        
        // Simpan state awal sebagai String agar aman dari strict equality
        initial_sub_jenis: String({{ Js::from(old('sub_jenis_ujian_id', $currentSubJenis?->id ?? '')) }}),
        initial_sub_indikator: String({{ Js::from(old('sub_indikator_id', $currentSubIndikator?->id ?? '')) }}),
        
        sistem_penilaian: {{ Js::from(old('_sistem_penilaian', $currentSubJenis?->sistem_penilaian ?? '')) }},
        jumlah_opsi: {{ Js::from((int) old('_jumlah_opsi', $currentSubJenis?->jumlah_jawaban_pilihan_ganda ?? 5)) }},
        nilai_benar_default: {{ Js::from($currentSubJenis?->nilai_benar ?? '') }},
        subJenisOptions: [],
        subIndikatorOptions: [],
        locked: {{ $locked ? 'true' : 'false' }},
        
        async loadSubJenis(preserve = false) {
            if (!preserve) { 
                this.sub_jenis_ujian_id = ''; 
                this.sub_indikator_id = ''; 
                this.resetMeta(); 
            }
            this.subJenisOptions = [];
            this.subIndikatorOptions = [];
            
            if (!this.jenis_ujian_id) return;
            
            const res = await fetch('{{ url('superadmin/soal/options/sub-jenis-ujian') }}/' + this.jenis_ujian_id);
            this.subJenisOptions = await res.json();
            
            if (preserve && this.initial_sub_jenis) { 
                // Gunakan setTimeout 50ms untuk menjamin x-for selesai merender DOM
                setTimeout(async () => {
                    this.sub_jenis_ujian_id = this.initial_sub_jenis;
                    this.applyMeta(); 
                    await this.loadSubIndikator(true); 
                }, 50);
            }
        },
        async loadSubIndikator(preserve = false) {
            if (!preserve) {
                this.sub_indikator_id = '';
            }
            this.applyMeta();
            this.subIndikatorOptions = [];
            
            if (!this.sub_jenis_ujian_id) return;
            
            const res = await fetch('{{ url('superadmin/soal/options/sub-indikator') }}/' + this.sub_jenis_ujian_id);
            this.subIndikatorOptions = await res.json();
            
            if (preserve && this.initial_sub_indikator) {
                setTimeout(() => {
                    this.sub_indikator_id = this.initial_sub_indikator;
                }, 50);
            }
        },
        applyMeta() {
            const opt = this.subJenisOptions.find(o => String(o.id) === String(this.sub_jenis_ujian_id));
            if (opt) {
                this.sistem_penilaian = opt.sistem_penilaian;
                this.jumlah_opsi = parseInt(opt.jumlah_jawaban_pilihan_ganda);
                this.nilai_benar_default = opt.nilai_benar;
            }
        },
        resetMeta() { this.sistem_penilaian = ''; this.jumlah_opsi = 5; this.nilai_benar_default = ''; },
        init() { 
            if (this.jenis_ujian_id) {
                this.loadSubJenis(true); 
            }
        }
    }"
    x-init="init()"
    class="space-y-6"
>
    <input type="hidden" name="_sistem_penilaian" :value="sistem_penilaian">
    <input type="hidden" name="_jumlah_opsi" :value="jumlah_opsi">
    @if(request()->has('ujian_id'))
        <input type="hidden" name="ujian_id" value="{{ request('ujian_id') }}">
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-secondary-900">Kategori Soal</h3>
        </div>
        <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Jenis Ujian <span class="text-danger-500">*</span></label>
                <select x-model="jenis_ujian_id" @change="loadSubJenis()" :disabled="locked" class="input w-full">
                    <option value="">-- Pilih --</option>
                    @foreach($jenisUjians as $jenisUjian)
                        <option value="{{ (string) $jenisUjian->id }}">{{ $jenisUjian->nama_jenis_ujian }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Sub Jenis Ujian <span class="text-danger-500">*</span></label>
                <select x-model="sub_jenis_ujian_id" @change="loadSubIndikator()" :disabled="locked" class="input w-full">
                    <option value="">-- Pilih --</option>
                    <template x-for="opt in subJenisOptions" :key="opt.id">
                        <option :value="String(opt.id)" x-text="opt.nama_sub_jenis_ujian"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Sub Indikator <span class="text-danger-500">*</span></label>
                <select name="sub_indikator_id" x-model="sub_indikator_id" :disabled="locked" class="input w-full @error('sub_indikator_id') border-danger-500 @enderror" required>
                    <option value="">-- Pilih --</option>
                    <template x-for="opt in subIndikatorOptions" :key="opt.id">
                        <option :value="String(opt.id)" x-text="opt.nama_sub_indikator"></option>
                    </template>
                </select>
                <template x-if="locked">
                    <input type="hidden" name="sub_indikator_id" :value="sub_indikator_id">
                </template>
                @error('sub_indikator_id')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-secondary-900">Butir Soal</h3>
        </div>
        <div class="card-body space-y-4">
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Teks Soal <span class="text-danger-500">*</span></label>
                <x-ckeditor-soal name="soal" :value="old('soal', $soal?->soal)" :required="true" rows="6" :error="$errors->has('soal')" />
                @error('soal')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Gambar Soal</label>
                <input type="file" name="gambar_soal" accept="image/*" class="input w-full">
                @if($soal?->gambar_soal)
                    <div x-data="{ hapus: false }" class="mt-2">
                        <input type="hidden" name="hapus_gambar_soal" :value="hapus ? '1' : '0'">
                        <div class="inline-flex flex-col gap-1.5" :class="hapus ? 'opacity-50' : ''">
                            <img src="{{ Storage::url($soal->gambar_soal) }}" class="h-24 rounded border">
                            <button type="button" @click="hapus = !hapus"
                                    class="inline-flex items-center gap-1 text-xs font-medium"
                                    :class="hapus ? 'text-secondary-500' : 'text-danger-600 hover:text-danger-700'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span x-text="hapus ? 'Batal hapus' : 'Hapus gambar'"></span>
                            </button>
                        </div>
                    </div>
                @endif
                @error('gambar_soal')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>

            @foreach(['a', 'b', 'c', 'd', 'e'] as $opt)
                <div x-show="'{{ $opt }}' !== 'e' || jumlah_opsi === 5" class="border border-secondary-200 rounded-lg p-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="font-semibold text-secondary-800 uppercase">Opsi {{ $opt }}</span>
                        <template x-if="sistem_penilaian === 'benar_salah'">
                            <label class="flex items-center gap-1 text-sm text-secondary-600">
                                <input type="radio" name="kunci_jawaban" value="{{ strtoupper($opt) }}"
                                       @checked(old('kunci_jawaban', $soal?->kunci_jawaban) === strtoupper($opt))>
                                Kunci Jawaban
                            </label>
                        </template>
                    </div>
                    <div class="mb-2">
                        <x-ckeditor-soal name="opsi_{{ $opt }}" :value="old('opsi_'.$opt, $soal?->{'opsi_'.$opt})" placeholder="Tulis opsi {{ strtoupper($opt) }}..." rows="2" :error="$errors->has('opsi_'.$opt)" />
                    </div>
                    @error('opsi_'.$opt)<p class="text-sm text-danger-600 mb-2">{{ $message }}</p>@enderror

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-secondary-600 mb-1">Gambar Opsi {{ strtoupper($opt) }}</label>
                            <input type="file" name="gambar_opsi_{{ $opt }}" accept="image/*" class="input w-full text-sm">
                            @if($soal?->{'gambar_opsi_'.$opt})
                                <div x-data="{ hapus: false }" class="mt-2">
                                    <input type="hidden" name="hapus_gambar_opsi_{{ $opt }}" :value="hapus ? '1' : '0'">
                                    <div class="inline-flex flex-col gap-1.5" :class="hapus ? 'opacity-50' : ''">
                                        <img src="{{ Storage::url($soal->{'gambar_opsi_'.$opt}) }}" class="h-16 rounded border">
                                        <button type="button" @click="hapus = !hapus"
                                                class="inline-flex items-center gap-1 text-xs font-medium"
                                                :class="hapus ? 'text-secondary-500' : 'text-danger-600 hover:text-danger-700'">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span x-text="hapus ? 'Batal hapus' : 'Hapus gambar'"></span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div x-show="sistem_penilaian === 'tiap_jawaban_ada_poin'">
                            <label class="block text-xs font-medium text-secondary-600 mb-1">Nilai Bobot {{ strtoupper($opt) }}</label>
                            <input type="number" step="0.01" name="nilai_bobot_{{ $opt }}"
                                   value="{{ old('nilai_bobot_'.$opt, $soal?->{'nilai_bobot_'.$opt}) }}"
                                   class="input w-full text-sm @error('nilai_bobot_'.$opt) border-danger-500 @enderror">
                            @error('nilai_bobot_'.$opt)<p class="text-sm text-danger-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            @endforeach

            @error('kunci_jawaban')<p class="text-sm text-danger-600">{{ $message }}</p>@enderror

            <div x-show="sistem_penilaian === 'benar_salah'">
                <label class="block text-sm font-medium text-secondary-700 mb-1">Nilai Bobot Benar (Override)</label>
                <input type="number" step="0.01" name="nilai_bobot_benar"
                       value="{{ old('nilai_bobot_benar', $soal?->nilai_bobot_benar) }}"
                       class="input w-full md:w-1/3 @error('nilai_bobot_benar') border-danger-500 @enderror"
                       :placeholder="nilai_benar_default ? ('Default: ' + nilai_benar_default) : 'Nilai default sub jenis ujian'">
                <p class="mt-1 text-xs text-secondary-400">Kosongkan untuk memakai nilai default dari sub jenis ujian.</p>
                @error('nilai_bobot_benar')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-secondary-900">Pembahasan</h3>
        </div>
        <div class="card-body space-y-4">
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Pembahasan</label>
                <x-wysiwyg name="pembahasan" :value="old('pembahasan', $soal?->pembahasan)" rows="4" :error="$errors->has('pembahasan')" />
            </div>
            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">Gambar Pembahasan</label>
                <input type="file" name="gambar_pembahasan" accept="image/*" class="input w-full">
                @if($soal?->gambar_pembahasan)
                    <div x-data="{ hapus: false }" class="mt-2">
                        <input type="hidden" name="hapus_gambar_pembahasan" :value="hapus ? '1' : '0'">
                        <div class="inline-flex flex-col gap-1.5" :class="hapus ? 'opacity-50' : ''">
                            <img src="{{ Storage::url($soal->gambar_pembahasan) }}" class="h-24 rounded border">
                            <button type="button" @click="hapus = !hapus"
                                    class="inline-flex items-center gap-1 text-xs font-medium"
                                    :class="hapus ? 'text-secondary-500' : 'text-danger-600 hover:text-danger-700'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span x-text="hapus ? 'Batal hapus' : 'Hapus gambar'"></span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('superadmin.soal.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</div>
