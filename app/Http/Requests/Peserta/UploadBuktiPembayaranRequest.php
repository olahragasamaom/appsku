<?php

namespace App\Http\Requests\Peserta;

use Illuminate\Foundation\Http\FormRequest;

class UploadBuktiPembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isPeserta();
    }

    public function rules(): array
    {
        return [
            'bukti_pembayaran' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            'catatan_peserta' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'bukti_pembayaran.required' => 'Bukti pembayaran wajib diupload',
            'bukti_pembayaran.image' => 'File harus berupa gambar',
            'bukti_pembayaran.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'bukti_pembayaran.max' => 'Ukuran gambar maksimal 5 MB',
            'catatan_peserta.max' => 'Catatan maksimal 500 karakter',
        ];
    }
}
