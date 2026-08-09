@props(['name', 'value' => '', 'required' => false, 'placeholder' => '', 'rows' => 4, 'error' => false])

<div x-data="wysiwygEditor({
        content: `{{ $value }}`,
        uploadUrl: '{{ route('superadmin.soal.upload-editor') }}'
    })"
    class="border {{ $error ? 'border-danger-500' : 'border-slate-300' }} rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500 bg-white">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-1 p-2 bg-slate-50 border-b border-slate-200">
        <button type="button" @click="format('bold')" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Bold">
            <span class="font-bold text-sm">B</span>
        </button>
        <button type="button" @click="format('italic')" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Italic">
            <span class="font-serif italic font-bold text-sm">I</span>
        </button>
        <button type="button" @click="format('underline')" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Underline">
            <span class="font-serif underline font-bold text-sm">U</span>
        </button>
        <div class="w-px h-5 bg-slate-300 mx-1"></div>
        <button type="button" @click="format('subscript')" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Subscript (X₂)">
            <span class="font-serif text-sm">X<sub>2</sub></span>
        </button>
        <button type="button" @click="format('superscript')" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Superscript (X²)">
            <span class="font-serif text-sm">X<sup>2</sup></span>
        </button>
        <div class="w-px h-5 bg-slate-300 mx-1"></div>
        {{-- Tombol Insert Gambar --}}
        <button type="button" @click="$refs.imgInput.click()" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Sisipkan Gambar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </button>
        <input type="file" x-ref="imgInput" accept="image/*" class="hidden" @change="uploadImage($event)">
        <div class="w-px h-5 bg-slate-300 mx-1"></div>
        <button type="button" @click="format('removeFormat')" class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-200 text-slate-700 transition-colors" title="Hapus Format">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </button>

        <template x-if="uploading">
            <span class="ml-2 text-xs text-primary-600 font-medium flex items-center gap-1">
                <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Mengunggah...
            </span>
        </template>
    </div>

    {{-- Editor Area (contenteditable) --}}
    <div x-ref="editor"
         contenteditable="true"
         @input="updateContent"
         @blur="updateContent"
         @click="handleClick($event)"
         class="wysiwyg-editor p-4 prose prose-sm max-w-none focus:outline-none"
         style="min-height: {{ $rows * 1.5 }}rem;"
         data-placeholder="{{ $placeholder }}">
    </div>

    {{-- Hidden Input to store the actual HTML value --}}
    <input type="hidden" name="{{ $name }}" :value="content" {{ $required ? 'required' : '' }}>
</div>

@pushonce('scripts')
<style>
    .wysiwyg-editor:empty:before {
        content: attr(data-placeholder);
        color: #94a3b8;
        pointer-events: none;
    }
    /* Gambar di dalam editor bisa dipilih & di-resize */
    .wysiwyg-editor img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        display: inline-block;
        cursor: pointer;
    }
    .wysiwyg-editor img.img-selected {
        outline: 3px solid #3b82f6;
        outline-offset: 2px;
        resize: both;
        overflow: hidden;
    }
</style>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('wysiwygEditor', (config) => ({
            content: config.content,
            uploadUrl: config.uploadUrl,
            uploading: false,
            savedRange: null,

            init() {
                this.$refs.editor.innerHTML = this.content;
                // Simpan posisi kursor terakhir agar gambar disisipkan di tempat yang benar
                this.$refs.editor.addEventListener('keyup', () => this.saveSelection());
                this.$refs.editor.addEventListener('mouseup', () => this.saveSelection());
            },

            saveSelection() {
                const sel = window.getSelection();
                if (sel.rangeCount > 0 && this.$refs.editor.contains(sel.anchorNode)) {
                    this.savedRange = sel.getRangeAt(0);
                }
            },

            restoreSelection() {
                if (this.savedRange) {
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(this.savedRange);
                }
            },

            format(command) {
                this.$refs.editor.focus();
                this.restoreSelection();
                document.execCommand(command, false, null);
                this.updateContent();
            },

            async uploadImage(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.uploading = true;
                const formData = new FormData();
                formData.append('gambar', file);

                try {
                    const res = await fetch(this.uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const data = await res.json();
                    if (data.url) {
                        this.insertImage(data.url);
                    }
                } catch (e) {
                    alert('Gagal mengunggah gambar.');
                } finally {
                    this.uploading = false;
                    event.target.value = ''; // reset input agar bisa upload file sama lagi
                }
            },

            insertImage(url) {
                this.$refs.editor.focus();
                this.restoreSelection();
                // Sisipkan gambar + baris baru agar kursor bisa lanjut mengetik di bawahnya
                const html = `<img src=\"${url}\" style=\"width: 300px;\"><p><br></p>`;
                document.execCommand('insertHTML', false, html);
                this.updateContent();
            },

            handleClick(event) {
                // Klik gambar untuk memilih & mengaktifkan mode resize (drag pojok kanan bawah)
                this.$refs.editor.querySelectorAll('img').forEach(img => img.classList.remove('img-selected'));
                if (event.target.tagName === 'IMG') {
                    event.target.classList.add('img-selected');
                }
            },

            updateContent() {
                // Bersihkan class 'img-selected' agar tidak ikut tersimpan
                const clone = this.$refs.editor.cloneNode(true);
                clone.querySelectorAll('img.img-selected').forEach(img => img.classList.remove('img-selected'));
                this.content = clone.innerHTML;
            }
        }));
    });
</script>
@endpushonce
