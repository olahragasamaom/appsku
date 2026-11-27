@props(['ujianSoal', 'jawaban', 'ujianPeserta' => null])

@php
    $soal = $ujianSoal->soal;
    $tipe_tampil = $soal->tipe_tampil ?? 'vertical';
    
    $mapping = ['a' => 'a', 'b' => 'b', 'c' => 'c', 'd' => 'd', 'e' => 'e'];
    if ($ujianPeserta && $ujianSoal->ujian->randomize_opsi_tkp) {
        $mapping = \App\Services\OpsiRandomizer::getMappingForPeserta(
            $ujianSoal->ujian,
            $ujianPeserta,
            $soal->id
        );
    }
    
    $displayOrder = \App\Services\OpsiRandomizer::getDisplayOrder(
        ['a' => $soal->opsi_a, 'b' => $soal->opsi_b, 'c' => $soal->opsi_c, 'd' => $soal->opsi_d, 'e' => $soal->opsi_e],
        $mapping
    );
@endphp

{{-- Opsi Jawaban --}}
@if($tipe_tampil === 'horizontal')
     {{-- Horizontal Layout (Side-by-side untuk soal analogi/pola) --}}
     <div class="mt-4 flex flex-wrap justify-center gap-2 sm:gap-3">
        @foreach(['A', 'B', 'C', 'D', 'E'] as $displayLabel)
            @php
                $originalKey = $displayOrder[strtolower($displayLabel)] ?? null;
                if (!$originalKey) continue;
                
                $opsiText = $soal->{'opsi_'.$originalKey};
                $gambarOpsi = $soal->{'gambar_opsi_'.$originalKey};
            @endphp
            
            @if($opsiText !== null && $opsiText !== '')
                <label class="flex flex-col items-center gap-2 p-3 border-2 rounded-lg cursor-pointer transition-all group"
                       :class="jawaban[{{ $ujianSoal->id }}] === '{{ $displayLabel }}' ? 'border-primary-500 bg-primary-50 shadow-md' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50'">
                    
                    <div class="flex items-center justify-center">
                        <input type="radio"
                               name="soal_{{ $ujianSoal->id }}"
                               value="{{ $displayLabel }}"
                               x-model="jawaban[{{ $ujianSoal->id }}]"
                               @change="save({{ $ujianSoal->id }}, '{{ $displayLabel }}')"
                               class="w-4 h-4 text-primary-600 border-slate-300 focus:ring-primary-600">
                    </div>
                    
                    @if($gambarOpsi)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($gambarOpsi) }}" 
                             alt="Opsi {{ $displayLabel }}" 
                             class="w-16 h-16 sm:w-20 sm:h-20 rounded-md border border-slate-200 object-cover group-hover:border-slate-400">
                    @else
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-md border-2 border-dashed border-slate-300 flex items-center justify-center bg-slate-50">
                            <span class="text-xs text-slate-400 text-center px-1">{{ \Illuminate\Support\Str::limit(strip_tags($opsiText), 20) }}</span>
                        </div>
                    @endif
                    
                    <span class="text-sm font-bold" :class="jawaban[{{ $ujianSoal->id }}] === '{{ $displayLabel }}' ? 'text-primary-700' : 'text-slate-700'">{{ $displayLabel }}</span>
                    
                    @if($opsiText && !$gambarOpsi)
                        <span class="text-xs text-slate-600 text-center line-clamp-2">{!! app('App\Services\FormulaRenderer')->render(app('App\Services\FormulaRenderer')->stripParagraphTags($opsiText)) !!}</span>
                    @endif
                </label>
            @endif
        @endforeach
    </div>
@else
     {{-- Vertical Layout (Default - Stack ke bawah) --}}
     <div class="mt-4 space-y-1.5">
        @foreach(['A', 'B', 'C', 'D', 'E'] as $displayLabel)
            @php
                $originalKey = $displayOrder[strtolower($displayLabel)] ?? null;
                if (!$originalKey) continue;
                
                $opsiText = $soal->{'opsi_'.$originalKey};
                $gambarOpsi = $soal->{'gambar_opsi_'.$originalKey};
            @endphp
            
             @if($opsiText !== null && $opsiText !== '')
                 <label class="flex items-start gap-2 p-2.5 border-2 rounded-lg cursor-pointer transition-all duration-150"
                       :class="jawaban[{{ $ujianSoal->id }}] === '{{ $displayLabel }}' ? 'border-primary-500 bg-primary-50 ring-2 ring-primary-200 shadow-sm' : 'border-slate-300 hover:bg-slate-50'">
                    
                    <div class="flex items-center h-6">
                        <input type="radio"
                               name="soal_{{ $ujianSoal->id }}"
                               value="{{ $displayLabel }}"
                               x-model="jawaban[{{ $ujianSoal->id }}]"
                               @change="save({{ $ujianSoal->id }}, '{{ $displayLabel }}')"
                               class="w-5 h-5 text-primary-600 border-slate-300 focus:ring-primary-600">
                    </div>
                    
                    <div class="flex-1 pt-0.5 flex flex-wrap gap-2">
                        <span class="text-sm font-bold mr-1.5 flex-shrink-0" :class="jawaban[{{ $ujianSoal->id }}] === '{{ $displayLabel }}' ? 'text-primary-700' : 'text-slate-700'">{{ $displayLabel }}.</span>
                        <span class="text-sm leading-relaxed flex-1" :class="jawaban[{{ $ujianSoal->id }}] === '{{ $displayLabel }}' ? 'text-primary-900 font-medium' : 'text-slate-700'">{!! app('App\Services\FormulaRenderer')->render(app('App\Services\FormulaRenderer')->stripParagraphTags($opsiText)) !!}</span>
                        
                        @if($gambarOpsi)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($gambarOpsi) }}" 
                                 alt="Opsi {{ $displayLabel }}" 
                                 class="mt-3 max-h-40 rounded-lg border border-slate-200 shadow-sm">
                        @endif
                    </div>
                </label>
            @endif
        @endforeach
    </div>
@endif
