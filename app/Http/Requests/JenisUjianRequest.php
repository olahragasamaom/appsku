<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FORM REQUEST: JenisUjianRequest
 * ================================
 * Form Request adalah kelas khusus untuk MEMVALIDASI input dari form
 * SEBELUM masuk ke controller. Idenya: controller jadi bersih, urusan
 * validasi dipisah ke sini.
 *
 * ALUR OTOMATIS LARAVEL:
 *   1. Kita "type-hint" kelas ini di parameter method controller,
 *      contoh: public function store(JenisUjianRequest $request)
 *   2. Sebelum isi method store() dijalankan, Laravel otomatis menjalankan
 *      authorize() lalu rules() di sini.
 *   3. Jika validasi GAGAL -> Laravel otomatis balik ke halaman form
 *      sambil membawa pesan error (tidak masuk ke controller sama sekali).
 *   4. Jika validasi LOLOS -> baru isi method controller dijalankan,
 *      dan kita ambil datanya dengan $request->validated().
 */
class JenisUjianRequest extends FormRequest
{
    /**
     * authorize(): apakah user ini BOLEH melakukan aksi ini?
     * return true = siapa pun yang lolos middleware boleh lanjut.
     * (Pengecekan hak akses superadmin sudah ditangani middleware di route.)
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * rules(): daftar ATURAN validasi per kolom.
     * Format: 'nama_kolom' => [aturan1, aturan2, ...]
     */
    public function rules(): array
    {
        // Ambil id dari model yang ada di URL (route model binding).
        // Saat CREATE -> null. Saat UPDATE -> id record yang sedang diedit.
        // Dipakai agar aturan "unique" tidak menganggap dirinya sendiri sebagai duplikat.
        $jenisUjianId = $this->route('jenisUjian')?->id;

        return [
            'nama_jenis_ujian' => [
                'required',            // wajib diisi
                'string',              // harus berupa teks
                'max:100',             // maksimal 100 karakter
                // unique: tidak boleh sama dengan nama yang sudah ada di tabel,
                // KECUALI record dengan id ini sendiri (penting saat edit).
                Rule::unique('panritta_jenis_ujian', 'nama_jenis_ujian')->ignore($jenisUjianId),
            ],
            'keterangan' => [
                'nullable',            // boleh kosong
                'string',
            ],
        ];
    }

    /**
     * messages(): mengganti pesan error bawaan (bahasa Inggris)
     * menjadi pesan kustom bahasa Indonesia.
     * Kunci format: 'nama_kolom.nama_aturan'.
     */
    public function messages(): array
    {
        return [
            'nama_jenis_ujian.required' => 'Jenis ujian wajib diisi.',
            'nama_jenis_ujian.max' => 'Jenis ujian maksimal 100 karakter.',
            'nama_jenis_ujian.unique' => 'Jenis ujian sudah digunakan.',
        ];
    }
}
