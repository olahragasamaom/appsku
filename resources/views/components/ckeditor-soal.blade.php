@props(['name', 'value' => '', 'required' => false, 'placeholder' => '', 'rows' => 6, 'error' => false])

{{-- CKEditor 5 (GPL, self-hosted via Vite) dengan Formula Modal di Toolbar --}}
@pushonce('scripts')
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.0/dist/katex.min.css">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!window.createSoalEditor) {
            console.error('CKEditor belum termuat');
            return;
        }

        // Auto-init all editors
        document.querySelectorAll('[data-ckeditor-soal]').forEach(function(el) {
            const inputEl = el.nextElementSibling;
            const editorId = el.getAttribute('data-ckeditor-soal');
            const initialValue = el.getAttribute('data-initial-value') || '';
            
            window.createSoalEditor(el, {
                value: initialValue,
                placeholder: el.getAttribute('data-placeholder'),
                uploadUrl: el.getAttribute('data-upload-url'),
                onChange: (data) => { inputEl.value = data; },
                editorId: editorId
            });
        });
    });

    window.showFormulaModal = function(editorId) {
        document.getElementById('modal_' + editorId).style.display = 'flex';
    };

    window.closeFormulaModal = function(editorId) {
        document.getElementById('modal_' + editorId).style.display = 'none';
    };

    window.insertPresetFormula = function(editorId, formula) {
        window.insertFormulaToSoalEditor(editorId, formula);
        window.closeFormulaModal(editorId);
    };

    window.showFormulaPreview = function(formula, previewId) {
        if (!window.katex) {
            console.error('KaTeX not loaded');
            return;
        }
        
        const previewEl = document.getElementById(previewId);
        if (!previewEl) return;

        try {
            const rendered = window.katex.renderToString(formula, { throwOnError: true });
            previewEl.innerHTML = rendered;
            previewEl.parentElement.parentElement.style.display = 'block';
        } catch (e) {
            previewEl.innerHTML = '<span style="color: #dc2626; font-size: 0.875rem;">❌ Invalid LaTeX</span>';
            previewEl.parentElement.parentElement.style.display = 'block';
        }
    };

    window.insertFormulaToSoalEditor = function(editorId, formula) {
        if (!formula || !formula.trim()) return;

        const editor = window._ckeditorInstances && window._ckeditorInstances[editorId];
        const inputEl = document.querySelector(`input[data-editor-id="${editorId}"]`);

        if (!editor || !inputEl) {
            alert('Editor tidak ditemukan');
            return;
        }

        if (!window.katex) {
            alert('KaTeX library belum dimuat. Silakan refresh halaman.');
            return;
        }

        try {
            // Validate formula dengan KaTeX
            window.katex.renderToString(formula, { throwOnError: true });

            // Insert formula sebagai raw LaTeX dengan delimiter khusus
            // Format: [[formula_latex_here]]
            // Preview button akan render ini dengan KaTeX
            const formulaText = `[[${formula}]]`;

            // Insert di posisi cursor saat ini (inline, bukan paragraph baru)
            editor.model.change((writer) => {
                const insertPosition = editor.model.document.selection.getFirstPosition();
                writer.insertText(formulaText, insertPosition);
            });

            // Update hidden input dengan data terbaru
            inputEl.value = editor.getData();
        } catch (e) {
            alert('Invalid LaTeX formula: ' + e.message);
        }
    };

    window.showPreviewModal = function(editorId) {
        const editor = window._ckeditorInstances && window._ckeditorInstances[editorId];
        if (!editor) {
            alert('Editor tidak ditemukan');
            return;
        }

        const content = editor.getData();
        const previewModal = document.getElementById('preview_modal_' + editorId);
        const previewContent = document.getElementById('preview_content_' + editorId);
        
        if (previewModal && previewContent) {
            if (!content || content.trim() === '') {
                previewContent.innerHTML = '<p style="color: #999;">Tidak ada konten untuk di-preview</p>';
            } else {
                // Replace [[formula]] dengan rendered KaTeX
                let html = content;
                html = html.replace(/\[\[([^\]]+)\]\]/g, function(match, formula) {
                    try {
                        return window.katex.renderToString(formula, { throwOnError: true });
                    } catch (e) {
                        return '<span style="color: #dc2626;">❌ ' + formula + '</span>';
                    }
                });
                
                previewContent.innerHTML = html;
            }
            previewModal.style.display = 'flex';
        }
    };

    window.closePreviewModal = function(editorId) {
        const previewModal = document.getElementById('preview_modal_' + editorId);
        if (previewModal) {
            previewModal.style.display = 'none';
        }
    };
</script>
<style>
    figure.image { max-width: 100%; margin: 1rem auto; text-align: center; }
    figure.image img { max-width: 100%; height: auto; border-radius: 8px; }
    figure.image.image_resized { display: block; box-sizing: border-box; }
    figure.image.image_resized img { width: 100%; }
    figure.image.image-style-align-left { float: left; margin: 0 1rem .5rem 0; }
    figure.image.image-style-align-right { float: right; margin: 0 0 .5rem 1rem; }
    figure.image.image-style-align-center { margin-left: auto; margin-right: auto; }

    span.math-formula { 
        display: inline-block;
        margin: 0 2px;
        padding: 2px 4px;
        border-radius: 3px;
    }

    /* Hide KaTeX MathML fallback text (jika KaTeX CSS tidak ter-load sempurna) */
    .katex-mathml,
    span.math-formula .katex-mathml,
    .ck-content .katex-mathml {
        position: absolute !important;
        clip: rect(1px, 1px, 1px, 1px) !important;
        padding: 0 !important;
        border: 0 !important;
        height: 1px !important;
        width: 1px !important;
        overflow: hidden !important;
    }

    .formula-preset-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .formula-preset-btn:hover {
        border-color: #2563eb;
        background: #eff6ff;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .modal-formula {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 50;
        padding: 1rem;
    }

    .modal-formula.show {
        display: flex;
    }

    .modal-formula-content {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        max-width: 50rem;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-formula-header {
        position: sticky;
        top: 0;
        background-color: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-formula-body {
        padding: 1.5rem;
    }

    .preview-modal {
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 50;
        padding: 1rem;
    }

    .preview-modal-content {
        background-color: white;
        border-radius: 0.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        max-width: 60rem;
        width: 100%;
        max-height: 80vh;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .preview-modal-header {
        position: sticky;
        top: 0;
        background-color: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .preview-modal-body {
        padding: 2rem;
        flex: 1;
        overflow-y: auto;
    }

    .preview-content {
        font-size: 1.1rem;
        line-height: 1.6;
        color: #333;
    }

    .preview-content p {
        margin: 1rem 0;
    }
</style>
@endpushonce

@php
    $editorId = 'editor_' . uniqid();
    $cleanValue = (string) $value;
    $cleanValue = html_entity_decode($cleanValue, ENT_QUOTES, 'UTF-8');
    $cleanValue = trim($cleanValue);
    if (str_starts_with($cleanValue, '<p>') && str_ends_with($cleanValue, '</p>')) {
        $cleanValue = substr($cleanValue, 3, -4);
    }
@endphp

<div class="rounded-xl overflow-hidden bg-white {{ $error ? 'border border-danger-500' : 'border border-slate-300' }}">
    <div data-ckeditor-soal="{{ $editorId }}"
         data-placeholder="{{ $placeholder ?: 'Tulis teks soal di sini...' }}"
         data-upload-url="{{ route('superadmin.soal.upload-editor') }}"
         data-initial-value="{{ e($cleanValue) }}"
         style="min-height: {{ max(6, (int) $rows) * 1.5 }}rem;"></div>
    <input type="hidden" name="{{ $name }}" data-editor-id="{{ $editorId }}" value="{{ e((string) $value) }}" {{ $required ? 'required' : '' }}>
</div>

{{-- Preview Modal --}}
<div id="preview_modal_{{ $editorId }}" class="preview-modal" onclick="if(event.target.id === this.id) window.closePreviewModal('{{ $editorId }}')">
    <div class="preview-modal-content">
        <div class="preview-modal-header">
            <h3 class="font-semibold text-secondary-900">📊 Preview Soal</h3>
            <button type="button" onclick="window.closePreviewModal('{{ $editorId }}')" class="text-secondary-500 hover:text-secondary-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="preview-modal-body">
            <div id="preview_content_{{ $editorId }}" class="preview-content"></div>
        </div>
    </div>
</div>

{{-- Formula Modal --}}
<div id="modal_{{ $editorId }}" class="modal-formula" onclick="if(event.target.id === this.id) window.closeFormulaModal('{{ $editorId }}')">
    <div class="modal-formula-content">
        <div class="modal-formula-header">
            <h3 class="font-semibold text-secondary-900">Pilih Formula Matematika</h3>
            <button type="button" onclick="window.closeFormulaModal('{{ $editorId }}')" class="text-secondary-500 hover:text-secondary-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="modal-formula-body space-y-4">
            {{-- Preset Formulas --}}
            <div>
                <h4 class="text-sm font-medium text-secondary-700 mb-3">📌 Formula Siap Pakai - Klik untuk Insert</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                    <button type="button" onclick="window.insertPresetFormula('{{ $editorId }}', 'x^2')" onmouseover="window.showFormulaPreview('x^2', 'preview_{{ $editorId }}')" class="formula-preset-btn">
                        <span class="text-lg">x<sup>2</sup></span>
                        <span class="text-xs text-secondary-600">Kuadrat</span>
                    </button>
                    <button type="button" onclick="window.insertPresetFormula('{{ $editorId }}', '\\sqrt{x}')" onmouseover="window.showFormulaPreview('\\sqrt{x}', 'preview_{{ $editorId }}')" class="formula-preset-btn">
                        <span class="text-lg">√x</span>
                        <span class="text-xs text-secondary-600">Akar</span>
                    </button>
                    <button type="button" onclick="window.insertPresetFormula('{{ $editorId }}', '\\frac{a}{b}')" onmouseover="window.showFormulaPreview('\\frac{a}{b}', 'preview_{{ $editorId }}')" class="formula-preset-btn">
                        <span class="text-lg">a/b</span>
                        <span class="text-xs text-secondary-600">Pecahan</span>
                    </button>
                    <button type="button" onclick="window.insertPresetFormula('{{ $editorId }}', 'E = mc^2')" onmouseover="window.showFormulaPreview('E = mc^2', 'preview_{{ $editorId }}')" class="formula-preset-btn">
                        <span class="text-lg">E=mc<sup>2</sup></span>
                        <span class="text-xs text-secondary-600">Einstein</span>
                    </button>
                    <button type="button" onclick="window.insertPresetFormula('{{ $editorId }}', '\\int_a^b f(x) dx')" onmouseover="window.showFormulaPreview('\\int_a^b f(x) dx', 'preview_{{ $editorId }}')" class="formula-preset-btn">
                        <span class="text-lg">∫</span>
                        <span class="text-xs text-secondary-600">Integral</span>
                    </button>
                    <button type="button" onclick="window.insertPresetFormula('{{ $editorId }}', '\\sum_{i=1}^{n} x_i')" onmouseover="window.showFormulaPreview('\\sum_{i=1}^{n} x_i', 'preview_{{ $editorId }}')" class="formula-preset-btn">
                        <span class="text-lg">Σ</span>
                        <span class="text-xs text-secondary-600">Sigma</span>
                    </button>
                    <button type="button" onclick="window.insertPresetFormula('{{ $editorId }}', '\\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}')" onmouseover="window.showFormulaPreview('\\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}', 'preview_{{ $editorId }}')" class="formula-preset-btn">
                        <span class="text-lg">ABC</span>
                        <span class="text-xs text-secondary-600">Rumus ABC</span>
                    </button>
                    <button type="button" onclick="window.insertPresetFormula('{{ $editorId }}', '\\lim_{x \\to \\infty} f(x)')" onmouseover="window.showFormulaPreview('\\lim_{x \\to \\infty} f(x)', 'preview_{{ $editorId }}')" class="formula-preset-btn">
                        <span class="text-lg">lim</span>
                        <span class="text-xs text-secondary-600">Limit</span>
                    </button>
                    <button type="button" onclick="window.insertPresetFormula('{{ $editorId }}', '\\sin^2 x + \\cos^2 x = 1')" onmouseover="window.showFormulaPreview('\\sin^2 x + \\cos^2 x = 1', 'preview_{{ $editorId }}')" class="formula-preset-btn">
                        <span class="text-lg">sin</span>
                        <span class="text-xs text-secondary-600">Trigonometri</span>
                    </button>
                </div>
            </div>

            {{-- Formula Preview --}}
            <div style="display: none;" class="bg-blue-50 border border-blue-200 rounded p-4">
                <p class="text-xs font-medium text-blue-900 mb-2">📊 Preview:</p>
                <div class="bg-white border border-blue-100 rounded p-3 min-h-12 flex items-center text-lg">
                    <div id="preview_{{ $editorId }}"></div>
                </div>
            </div>

            {{-- Info --}}
            <div class="bg-blue-50 border border-blue-200 rounded p-3 text-xs text-blue-800">
                <strong>💡 Info:</strong> Formula akan ditampilkan secara visual dan indah di soal untuk peserta ujian.
            </div>
        </div>
    </div>
</div>

<script>
    // Add formula & preview buttons to CKEditor toolbar setelah editor ter-load
    setTimeout(function() {
        const editorId = '{{ $editorId }}';
        const editor = window._ckeditorInstances && window._ckeditorInstances[editorId];
        
        if (editor && editor.ui && editor.ui.view && editor.ui.view.toolbar) {
            const toolbarElement = editor.ui.view.toolbar.element;
            
            // Add formula button
            if (toolbarElement && !document.getElementById('btn_formula_' + editorId)) {
                const btnFormula = document.createElement('button');
                btnFormula.id = 'btn_formula_' + editorId;
                btnFormula.type = 'button';
                btnFormula.innerHTML = '∑ Formula';
                btnFormula.className = 'ck-button';
                btnFormula.style.cssText = 'padding: 8px 12px; margin: 0 4px; border: 1px solid #c4c4c4; background: #f5f5f5; border-radius: 2px; cursor: pointer; font-weight: 600; color: #333;';
                btnFormula.onmouseover = function() { this.style.background = '#e8e8e8'; };
                btnFormula.onmouseout = function() { this.style.background = '#f5f5f5'; };
                btnFormula.onclick = function(e) {
                    e.preventDefault();
                    window.showFormulaModal('{{ $editorId }}');
                };
                toolbarElement.appendChild(btnFormula);
            }

            // Add preview button
            if (toolbarElement && !document.getElementById('btn_preview_' + editorId)) {
                const btnPreview = document.createElement('button');
                btnPreview.id = 'btn_preview_' + editorId;
                btnPreview.type = 'button';
                btnPreview.innerHTML = '👁️ Preview';
                btnPreview.className = 'ck-button';
                btnPreview.style.cssText = 'padding: 8px 12px; margin: 0 4px; border: 1px solid #c4c4c4; background: #f5f5f5; border-radius: 2px; cursor: pointer; font-weight: 600; color: #333;';
                btnPreview.onmouseover = function() { this.style.background = '#e8e8e8'; };
                btnPreview.onmouseout = function() { this.style.background = '#f5f5f5'; };
                btnPreview.onclick = function(e) {
                    e.preventDefault();
                    window.showPreviewModal('{{ $editorId }}');
                };
                toolbarElement.appendChild(btnPreview);
            }
        }
    }, 500);
</script>
