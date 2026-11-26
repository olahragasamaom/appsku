<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePesertaOfflineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nomor_peserta' => ['required', 'string', 'max:50'],
            'nama_peserta' => ['required', 'string', 'max:255'],
            'nama_kelas' => ['nullable', 'string', 'max:255'],
            'nomor_hp' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_peserta.required' => 'Nomor peserta wajib diisi.',
            'nama_peserta.required' => 'Nama peserta wajib diisi.',
        ];
    }
}
