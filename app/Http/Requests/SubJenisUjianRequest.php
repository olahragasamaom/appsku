<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FORM REQUEST: SubJenisUjianRequest
 * ===================================
 * Validasi input untuk tambah/edit Sub Jenis Ujian.
 * Pola sama seperti JenisUjianRequest, tapi dengan lebih banyak kolom
 * dan beberapa aturan menarik: "exists" dan "in".
 */
class SubJenisUjianRequest extends FormRequest
{
    /** Izinkan semua yang lolos middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // exists:tabel,kolom -> nilai HARUS ada di database.
            // Artinya: jenis_ujian_id yang dikirim wajib menunjuk ke JenisUjian nyata.
            'jenis_ujian_id' => ['required', 'exists:panritta_jenis_ujian,id'],
            'nama_sub_jenis_ujian' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'urutan' => ['nullable', 'integer', 'min:0'],
            // in:a,b -> nilai HANYA boleh salah satu dari daftar ini.
            'sistem_penilaian' => ['required', 'in:benar_salah,tiap_jawaban_ada_poin'],
            'jumlah_jawaban_pilihan_ganda' => ['required', 'in:4,5'],
            'nilai_benar' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jenis_ujian_id.required' => 'Jenis ujian wajib dipilih.',
            'jenis_ujian_id.exists' => 'Jenis ujian tidak valid.',
            'nama_sub_jenis_ujian.required' => 'Nama sub jenis ujian wajib diisi.',
            'nama_sub_jenis_ujian.max' => 'Nama sub jenis ujian maksimal 255 karakter.',
            'urutan.integer' => 'Urutan harus berupa angka.',
            'urutan.min' => 'Urutan minimal 0.',
            'sistem_penilaian.required' => 'Sistem penilaian wajib dipilih.',
            'sistem_penilaian.in' => 'Sistem penilaian tidak valid.',
            'jumlah_jawaban_pilihan_ganda.required' => 'Jumlah jawaban pilihan ganda wajib dipilih.',
            'jumlah_jawaban_pilihan_ganda.in' => 'Jumlah jawaban pilihan ganda harus 4 atau 5.',
            'nilai_benar.required' => 'Nilai benar wajib diisi.',
            'nilai_benar.numeric' => 'Nilai benar harus berupa angka.',
        ];
    }
}
