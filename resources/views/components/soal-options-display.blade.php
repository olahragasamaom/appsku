@props(['ujianSoal', 'jawaban'])

@php
    $soal = $ujianSoal->soal;
    $tipe_tampil = $soal->tipe_tampil ?? 'vertical';
@endphp

{{-- Opsi Jawaban --}}
@if($tipe_tampil === 'horizontal')
    {{-- Horizontal Layout (Side-by-side untuk soal analogi/pola) --}}
    <div class="mt-8 flex flex-wrap justify-center gap-3 sm:gap-4">
        @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
            @php 
                $opsiText = $soal->{'opsi_'.strtolower($opsi)}; 
                $gambarOpsi = $soal->{'gambar_opsi_'.strtolower($opsi)};
            @endphp
            
            @if($opsiText !== null && $opsiText !== '')
                <label class="flex flex-col items-center gap-2 p-3 border-2 rounded-lg cursor-pointer transition-all group"
                       :class="jawaban[{{ $ujianSoal->id }}] === '{{ $opsi }}' ? 'border-primary-500 bg-primary-50 shadow-md' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'">
                    
                    <div class="flex items-center justify-center">
                        <input type="radio"
                               name="soal_{{ $ujianSoal->id }}"
                               value="{{ $opsi }}"
                               x-model="jawaban[{{ $ujianSoal->id }}]"
                               @change="save({{ $ujianSoal->id }}, '{{ $opsi }}')"
                               class="w-4 h-4 text-primary-600 border-slate-300 focus:ring-primary-600">
                    </div>
                    
                    @if($gambarOpsi)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($gambarOpsi) }}" 
                             alt="Opsi {{ $opsi }}" 
                             class="w-16 h-16 sm:w-20 sm:h-20 rounded-md border border-slate-200 object-cover group-hover:border-slate-400">
                    @else
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-md border-2 border-dashed border-slate-300 flex items-center justify-center bg-slate-50">
                            <span class="text-xs text-slate-400 text-center px-1">{{ \Illuminate\Support\Str::limit($opsiText, 20) }}</span>
                        </div>
                    @endif
                    
                    <span class="text-sm font-bold" :class="jawaban[{{ $ujianSoal->id }}] === '{{ $opsi }}' ? 'text-primary-700' : 'text-slate-700'">{{ $opsi }}</span>
                    
                    @if($opsiText && !$gambarOpsi)
                        <span class="text-xs text-slate-600 text-center line-clamp-2">{{ $opsiText }}</span>
                    @endif
                </label>
            @endif
        @endforeach
    </div>
@else
    {{-- Vertical Layout (Default - Stack ke bawah) --}}
    <div class="mt-8 space-y-3">
        @foreach(['A', 'B', 'C', 'D', 'E'] as $opsi)
            @php 
                $opsiText = $soal->{'opsi_'.strtolower($opsi)}; 
                $gambarOpsi = $soal->{'gambar_opsi_'.strtolower($opsi)};
            @endphp
            
            @if($opsiText !== null && $opsiText !== '')
                <label class="flex items-start gap-4 p-4 border rounded-xl cursor-pointer transition-colors group"
                       :class="jawaban[{{ $ujianSoal->id }}] === '{{ $opsi }}' ? 'border-primary-500 bg-primary-50 shadow-sm ring-1 ring-primary-500' : 'border-slate-200 hover:bg-slate-50'">
                    
                    <div class="flex items-center h-6">
                        <input type="radio"
                               name="soal_{{ $ujianSoal->id }}"
                               value="{{ $opsi }}"
                               x-model="jawaban[{{ $ujianSoal->id }}]"
                               @change="save({{ $ujianSoal->id }}, '{{ $opsi }}')"
                               class="w-5 h-5 text-primary-600 border-slate-300 focus:ring-primary-600">
                    </div>
                    
                    <div class="flex-1 pt-0.5">
                        <span class="text-base font-bold mr-2" :class="jawaban[{{ $ujianSoal->id }}] === '{{ $opsi }}' ? 'text-primary-700' : 'text-slate-700'">{{ $opsi }}.</span>
                        <span class="text-base" :class="jawaban[{{ $ujianSoal->id }}] === '{{ $opsi }}' ? 'text-primary-900 font-medium' : 'text-slate-700'">{{ $opsiText }}</span>
                        
                        @if($gambarOpsi)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($gambarOpsi) }}" 
                                 alt="Opsi {{ $opsi }}" 
                                 class="mt-3 max-h-40 rounded-lg border border-slate-200 shadow-sm">
                        @endif
                    </div>
                </label>
            @endif
        @endforeach
    </div>
@endif
