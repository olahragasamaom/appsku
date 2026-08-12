@props(['name', 'value' => '', 'required' => false, 'placeholder' => '', 'rows' => 6, 'error' => false])

{{-- CKEditor 5 (GPL, self-hosted via Vite). Dipakai KHUSUS untuk inputan Teks Soal.
     Factory global window.createSoalEditor didefinisikan di resources/js/ckeditor-soal.js --}}
@pushonce('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ckeditorSoal', (config) => ({
            init() {
                // Simpan instance editor DI LUAR proxy Alpine agar CKEditor tidak
                // bentrok dengan reaktivitas (menghindari error proxy '_events').
                let editor = null;
                let created = false;
                const valueInput = this.$refs.valueInput;

                const start = (tries = 100) => {
                    if (created) return;
                    if (window.createSoalEditor) {
                        created = true;
                        window.createSoalEditor(this.$refs.editor, {
                            value: config.value,
                            placeholder: config.placeholder,
                            uploadUrl: config.uploadUrl,
                            onChange: (data) => { valueInput.value = data; },
                        }).then((instance) => {
                            editor = instance;
                        }).catch((e) => {
                            created = false;
                            console.error('CKEditor gagal dibuat:', e);
                            this.$refs.editor.innerHTML =
                                '<p style="padding:12px;color:#dc2626;font-size:.85rem">CKEditor gagal dimuat: ' +
                                (e && e.message ? e.message : e) + '</p>';
                        });
                    } else if (tries > 0) {
                        setTimeout(() => start(tries - 1), 50);
                    } else {
                        this.$refs.editor.innerHTML =
                            '<p style="padding:12px;color:#dc2626;font-size:.85rem">CKEditor belum termuat. Jalankan npm run build.</p>';
                    }
                };

                this.$nextTick(() => start());
            },
        }));
    });
</script>
<style>
    figure.image { max-width: 100%; margin: 1rem auto; text-align: center; }
    figure.image img { max-width: 100%; height: auto; border-radius: 8px; }
    figure.image.image_resized { display: block; box-sizing: border-box; }
    figure.image.image_resized img { width: 100%; }
    figure.image.image-style-align-left { float: left; margin: 0 1rem .5rem 0; }
    figure.image.image-style-align-right { float: right; margin: 0 0 .5rem 1rem; }
    figure.image.image-style-align-center { margin-left: auto; margin-right: auto; }
</style>
@endpushonce

<div
    x-data="ckeditorSoal({{ Js::from([
        'value' => (string) $value,
        'placeholder' => (string) ($placeholder ?: 'Tulis teks soal di sini...'),
        'uploadUrl' => route('superadmin.soal.upload-editor'),
    ]) }})"
    class="rounded-xl overflow-hidden bg-white {{ $error ? 'border border-danger-500' : 'border border-slate-300' }}">
    <div x-ref="editor" style="min-height: {{ max(6, (int) $rows) * 1.5 }}rem;"></div>
    <input type="hidden" name="{{ $name }}" x-ref="valueInput" value="{{ e((string) $value) }}" {{ $required ? 'required' : '' }}>
</div>
