@php
    $ujian = $ujian ?? null;
    $selectedJenisList = $selectedJenis ?? old('jenis_ujian_id', []);
    $firstSelectedJenis = !empty($selectedJenisList) ? (int) (is_array($selectedJenisList) ? reset($selectedJenisList) : $selectedJenisList) : '';
    $passingGrades = $passingGrades ?? collect(old('passing_grade', []));
    $firstPassingGrade = $firstSelectedJenis ? ($passingGrades[$firstSelectedJenis] ?? '') : '';
    $selectedAkses = old('akses_member', $ujian?->akses_member ?? []);
@endphp

<div x-data="{
        tipe: '{{ old('tipe_ujian', $ujian->tipe_ujian ?? 'offline_kelas') }}',
        selectedJenis: '{{ $firstSelectedJenis }}',
        jenisUjiansData: {{ Js::from($jenisUjians) }},
        get activeJenis() {
            if (!this.selectedJenis) return null;
            return this.jenisUjiansData.find(j => j.id == this.selectedJenis);
        }
     }"
     class="space-y-6">

    {{-- Parameter Utama --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-secondary-900">Parameter Utama</h3>
        </div>
        <div class="card-body space-y-4">
            <div>
                <label for="nama_ujian" class="block text-sm font-medium text-secondary-700 mb-1">
                    Nama Ujian <span class="text-danger-500">*</span>
                </label>
                <input type="text" name="nama_ujian" id="nama_ujian"
                       value="{{ old('nama_ujian', $ujian->nama_ujian ?? '') }}"
                       class="input w-full @error('nama_ujian') border-danger-500 @enderror"
                       placeholder="Tryout CPNS Nasional Gelombang 1" required>
                @error('nama_ujian')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-secondary-700 mb-1">
                    Tipe Ujian <span class="text-danger-500">*</span>
                </label>
                <select name="tipe_ujian" x-model="tipe" class="input w-full">
                    <option value="offline_kelas">Offline di Kelas</option>
                    <option value="online_paket">Online Paket</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="jumlah_soal" class="block text-sm font-medium text-secondary-700 mb-1">
                        Jumlah Soal <span class="text-danger-500">*</span>
                    </label>
                    <input type="number" name="jumlah_soal" id="jumlah_soal" min="0"
                           value="{{ old('jumlah_soal', $ujian->jumlah_soal ?? 0) }}"
                           class="input w-full @error('jumlah_soal') border-danger-500 @enderror" required>
                    @error('jumlah_soal')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Status</label>
                    <select name="status" class="input w-full">
                        <option value="draft" @selected(old('status', $ujian->status ?? 'draft') === 'draft')>Draft</option>
                        <option value="aktif" @selected(old('status', $ujian->status ?? '') === 'aktif')>Aktif</option>
                        <option value="selesai" @selected(old('status', $ujian->status ?? '') === 'selesai')>Selesai</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-6">
                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="acak_soal" value="0">
                    <input type="checkbox" name="acak_soal" value="1" class="rounded border-secondary-300 text-primary-600"
                           @checked(old('acak_soal', $ujian->acak_soal ?? false))>
                    <span class="text-sm text-secondary-700">Acak Soal</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="hidden" name="tampilkan_hasil" value="0">
                    <input type="checkbox" name="tampilkan_hasil" value="1" class="rounded border-secondary-300 text-primary-600"
                           @checked(old('tampilkan_hasil', $ujian->tampilkan_hasil ?? true))>
                    <span class="text-sm text-secondary-700">Tampilkan Hasil ke Peserta</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Jenis Ujian + Passing Grade --}}
    <div class="card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-secondary-900">Jenis Ujian &amp; Passing Grade</h3>
        </div>
        <div class="card-body space-y-4">
            @error('jenis_ujian_id')<p class="text-sm text-danger-600 mb-2">{{ $message }}</p>@enderror
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Pilih Jenis Ujian <span class="text-danger-500">*</span></label>
                    <select name="jenis_ujian_id[]" x-model="selectedJenis" class="input w-full" required>
                        <option value="">-- Pilih Jenis Ujian --</option>
                        <template x-for="jenis in jenisUjiansData" :key="jenis.id">
                            <option :value="jenis.id" x-text="jenis.nama_jenis_ujian" :selected="selectedJenis == jenis.id"></option>
                        </template>
                    </select>
                </div>
                <div x-show="selectedJenis" x-cloak>
                    <label class="block text-sm font-medium text-secondary-700 mb-1">Passing Grade (Opsional)</label>
                    <input type="number" step="0.01" min="0" 
                           :name="`passing_grade[${selectedJenis}]`"
                           value="{{ $firstPassingGrade }}"
                           class="input w-full" 
                           placeholder="Contoh: 300">
                </div>
            </div>

            {{-- Hierarki Sub Jenis & Sub Indikator --}}
            <div x-show="activeJenis" x-cloak class="mt-6 border-t border-secondary-100 pt-4">
                <h4 class="font-medium text-secondary-800 mb-3" x-text="`Struktur Soal: ${activeJenis?.nama_jenis_ujian}`"></h4>
                
                <template x-if="activeJenis && activeJenis.sub_jenis_ujian.length === 0">
                    <p class="text-sm text-secondary-500 italic">Belum ada sub jenis ujian.</p>
                </template>

                <div class="space-y-4">
                    <template x-for="subJenis in activeJenis?.sub_jenis_ujian" :key="subJenis.id">
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="font-bold text-slate-800" x-text="subJenis.nama_sub_jenis_ujian"></span>
                                <span class="text-xs bg-slate-200 text-slate-600 px-2 py-0.5 rounded-md" x-text="subJenis.sistem_penilaian === 'benar_salah' ? 'Benar-Salah' : 'Poin per Jawaban'"></span>
                            </div>

                            <template x-if="subJenis.sub_indikator.length === 0">
                                <p class="text-xs text-slate-500 italic ml-2">Belum ada sub indikator.</p>
                            </template>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <template x-for="indikator in subJenis.sub_indikator" :key="indikator.id">
                                    <div class="bg-white border border-slate-200 rounded p-3 flex flex-col justify-between shadow-sm">
                                        <p class="text-sm font-medium text-slate-700 mb-3" x-text="indikator.nama_sub_indikator"></p>
                                        <a :href="`{{ route('superadmin.soal.create') }}?sub_indikator_id=${indikator.id}`" 
                                           target="_blank"
                                           class="btn btn-primary btn-sm w-full inline-flex justify-center text-xs">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Tambah Soal
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Konfigurasi Offline --}}
    <div class="card" x-show="tipe === 'offline_kelas'" x-cloak>
        <div class="card-header">
            <h3 class="text-lg font-semibold text-secondary-900">Konfigurasi Offline di Kelas</h3>
        </div>
        <div class="card-body grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="tanggal_ujian" class="block text-sm font-medium text-secondary-700 mb-1">Tanggal &amp; Jam Ujian</label>
                <input type="datetime-local" name="tanggal_ujian" id="tanggal_ujian"
                       value="{{ old('tanggal_ujian', optional($ujian?->tanggal_ujian)->format('Y-m-d\TH:i')) }}"
                       class="input w-full @error('tanggal_ujian') border-danger-500 @enderror">
                @error('tanggal_ujian')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="durasi_ujian" class="block text-sm font-medium text-secondary-700 mb-1">Durasi (menit)</label>
                <input type="number" name="durasi_ujian" id="durasi_ujian" min="1"
                       value="{{ old('durasi_ujian', $ujian->durasi_ujian ?? '') }}"
                       class="input w-full @error('durasi_ujian') border-danger-500 @enderror">
                @error('durasi_ujian')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="batas_keterlambatan" class="block text-sm font-medium text-secondary-700 mb-1">Batas Keterlambatan</label>
                <input type="datetime-local" name="batas_keterlambatan" id="batas_keterlambatan"
                       value="{{ old('batas_keterlambatan', optional($ujian?->batas_keterlambatan)->format('Y-m-d\TH:i')) }}"
                       class="input w-full @error('batas_keterlambatan') border-danger-500 @enderror">
                @error('batas_keterlambatan')<p class="mt-1 text-sm text-danger-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="token_ujian" class="block text-sm font-medium text-secondary-700 mb-1">Token Ujian</label>
                <input type="text" name="token_ujian" id="token_ujian" maxlength="50"
                       value="{{ old('token_ujian', $ujian->token_ujian ?? '') }}"
                       class="input w-full" placeholder="Kosongkan untuk generate otomatis">
            </div>
        </div>
    </div>

    {{-- Konfigurasi Online --}}
    <div class="card" x-show="tipe === 'online_paket'" x-cloak>
        <div class="card-header">
            <h3 class="text-lg font-semibold text-secondary-900">Konfigurasi Online Paket</h3>
        </div>
        <div class="card-body">
            <label class="block text-sm font-medium text-secondary-700 mb-2">Akses Member</label>
            @error('akses_member')<p class="mb-2 text-sm text-danger-600">{{ $message }}</p>@enderror
            <div class="flex flex-wrap gap-4">
                @foreach($aksesMemberOptions as $member)
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="akses_member[]" value="{{ $member }}"
                               class="rounded border-secondary-300 text-primary-600"
                               @checked(in_array($member, (array) $selectedAkses, true))>
                        <span class="text-sm text-secondary-700">{{ $member }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('superadmin.ujian.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel ?? 'Simpan' }}</button>
    </div>
</div>
