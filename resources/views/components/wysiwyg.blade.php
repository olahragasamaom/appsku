@props(['name', 'value' => '', 'required' => false, 'placeholder' => '', 'rows' => 4, 'error' => false])

<div x-data="wysiwygEditor({
        content: `{{ $value }}`
    })" 
    class="border {{ $error ? 'border-danger-500' : 'border-slate-300' }} rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500 bg-white">
    
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-1 p-2 bg-slate-50 border-b border-slate-200">
        <button type="button" @click="format('bold')" class="p-1.5 rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Bold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h8a4 4 0 100-8H6v8zm0 0h9a4 4 0 110 8H6v-8z"/></svg>
        </button>
        <button type="button" @click="format('italic')" class="p-1.5 rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Italic">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg> {{-- Italic icon approx --}}
            <span class="font-serif italic font-bold text-sm leading-none block w-4 text-center">I</span>
        </button>
        <button type="button" @click="format('underline')" class="p-1.5 rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Underline">
            <span class="font-serif underline font-bold text-sm leading-none block w-4 text-center">U</span>
        </button>
        <div class="w-px h-5 bg-slate-300 mx-1"></div>
        <button type="button" @click="format('subscript')" class="p-1.5 rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Subscript (X₂)">
            <span class="font-serif text-sm leading-none block w-4 text-center">X<sub>2</sub></span>
        </button>
        <button type="button" @click="format('superscript')" class="p-1.5 rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Superscript (X²)">
            <span class="font-serif text-sm leading-none block w-4 text-center">X<sup>2</sup></span>
        </button>
        <div class="w-px h-5 bg-slate-300 mx-1"></div>
        <button type="button" @click="format('removeFormat')" class="p-1.5 rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Hapus Format">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </button>
    </div>

    {{-- Editor Area (contenteditable) --}}
    <div x-ref="editor" 
         contenteditable="true" 
         @input="updateContent"
         @blur="updateContent"
         class="p-4 prose prose-sm max-w-none focus:outline-none min-h-[{{ $rows * 1.5 }}rem]"
         style="min-height: {{ $rows * 1.5 }}rem;">
    </div>

    {{-- Hidden Input to store the actual HTML value --}}
    <input type="hidden" name="{{ $name }}" :value="content" {{ $required ? 'required' : '' }}>
</div>

@pushonce('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('wysiwygEditor', (config) => ({
            content: config.content,
            init() {
                this.$refs.editor.innerHTML = this.content;
            },
            format(command) {
                document.execCommand(command, false, null);
                this.updateContent();
                this.$refs.editor.focus();
            },
            updateContent() {
                this.content = this.$refs.editor.innerHTML;
            }
        }));
    });
</script>
@endpushonce
