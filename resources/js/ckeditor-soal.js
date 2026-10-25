import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Heading,
    Bold,
    Italic,
    Underline,
    List,
    BlockQuote,
    Link,
    Image,
    ImageToolbar,
    ImageUpload,
    ImageStyle,
    ImageResize,
    ImageCaption,
    Undo,
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';
import 'katex/dist/katex.css';

import katex from 'katex';

/**
 * Get preview HTML dari formula LaTeX menggunakan KaTeX.
 * Digunakan untuk preview di modal sebelum insert ke editor.
 */
window.getFormulaPreview = function (formula) {
    if (!formula || !formula.trim()) {
        return '';
    }

    try {
        return katex.renderToString(formula, { throwOnError: true });
    } catch (e) {
        return `<span style="color: #dc2626; font-size: 0.875rem;">❌ Error: ${e.message}</span>`;
    }
};

/**
 * Upload adapter yang mengirim gambar ke endpoint Laravel dan
 * mengembalikan URL publik untuk disematkan sebagai <img>.
 */
function makeUploadAdapter(loader, uploadUrl) {
    return {
        upload() {
            return loader.file.then(
                (file) =>
                    new Promise((resolve, reject) => {
                        const data = new FormData();
                        data.append('gambar', file);

                        fetch(uploadUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                Accept: 'application/json',
                            },
                            body: data,
                        })
                            .then((r) => r.json())
                            .then((d) => (d.url ? resolve({ default: d.url }) : reject('Upload gagal')))
                            .catch(() => reject('Upload gagal'));
                    })
            );
        },
        abort() {},
    };
}

/**
 * Buat CKEditor 5 (GPL, self-hosted) untuk inputan Teks Soal.
 *
 * @param {HTMLElement} el         Elemen editor.
 * @param {Object}      options    { value, placeholder, uploadUrl, onChange }
 * @returns {Promise<ClassicEditor>}
 */
window.createSoalEditor = async function (el, options) {
    const { value = '', placeholder = '', uploadUrl = '', onChange = () => {} } = options || {};

    const editor = await ClassicEditor.create(el, {
        licenseKey: 'GPL',
        initialData: value,
        plugins: [
            Essentials,
            Paragraph,
            Heading,
            Bold,
            Italic,
            Underline,
            List,
            BlockQuote,
            Link,
            Image,
            ImageToolbar,
            ImageUpload,
            ImageStyle,
            ImageResize,
            ImageCaption,
            Undo,
        ],
        toolbar: [
            'heading',
            '|',
            'bold',
            'italic',
            'underline',
            '|',
            'bulletedList',
            'numberedList',
            '|',
            'blockQuote',
            'link',
            '|',
            'imageUpload',
            '|',
            'undo',
            'redo',
        ],
        image: {
            resizeUnit: 'px',
            toolbar: [
                'imageStyle:alignLeft',
                'imageStyle:alignCenter',
                'imageStyle:alignRight',
                '|',
                'resizeImage',
                '|',
                'toggleImageCaption',
                'imageTextAlternative',
            ],
        },
        placeholder,
    });

    editor.plugins.get('FileRepository').createUploadAdapter = (loader) =>
        makeUploadAdapter(loader, uploadUrl);

    window.insertMathFormula = function (editor, valueInput, formula) {
        if (!formula || !formula.trim()) {
            return;
        }

        try {
            const rendered = katex.renderToString(formula, { throwOnError: true });

            if (!editor || !editor.getData) {
                console.error('Editor instance not valid');
                return;
            }

            const currentData = editor.getData();
            const safeFormula = formula.replace(/"/g, '&quot;');
            const formulaHtml = `<p><span class="math-formula" data-formula="${safeFormula}">${rendered}</span></p>`;

            const newData = currentData + formulaHtml;

            editor.setData(newData);

            if (valueInput && valueInput.value !== undefined) {
                valueInput.value = newData;
            }
        } catch (e) {
            alert('Invalid LaTeX formula: ' + e.message);
        }
    };

    // Store instance dengan ID unik
    if (!window._ckeditorInstances) {
        window._ckeditorInstances = {};
    }
    if (options.editorId) {
        window._ckeditorInstances[options.editorId] = editor;
    }

    editor.model.document.on('change:data', () => onChange(editor.getData()));
    onChange(editor.getData());

    return editor;
};
