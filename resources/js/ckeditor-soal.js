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

    el.innerHTML = value;

    const editor = await ClassicEditor.create(el, {
        licenseKey: 'GPL',
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

    editor.model.document.on('change:data', () => onChange(editor.getData()));
    onChange(editor.getData());

    return editor;
};
