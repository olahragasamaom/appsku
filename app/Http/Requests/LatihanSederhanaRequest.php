<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LatihanSederhanaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Kita return true karena authorization akan ditangani oleh route middleware ['superadmin']
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk form sederhana.
     * Kita gunakan array-based rules sesuai konvensi project.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'kode' => ['required', 'string', 'max:50'],
            'penulis' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom error messages dalam bahasa Indonesia
     */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul wajib diisi.',
            'kode.required' => 'Kode wajib diisi.',
            'penulis.required' => 'Penulis wajib diisi.',
            'judul.max' => 'Judul terlalu panjang (max 255).',
        ];
    }
}
