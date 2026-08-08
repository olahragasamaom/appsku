<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginPesertaOfflineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'nomor_peserta' => ['required', 'string', 'max:50'],
            'kode_akses' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_peserta.required' => 'Nomor peserta wajib diisi.',
            'kode_akses.required' => 'Kode akses wajib diisi.',
        ];
    }
}
