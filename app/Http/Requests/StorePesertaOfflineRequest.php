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
        $ujianId = $this->route('ujian')?->id;

        return [
            'nomor_peserta' => [
                'required', 'string', 'max:50',
                Rule::unique('panritta_peserta_offline', 'nomor_peserta')
                    ->where(fn ($query) => $query->where('ujian_id', $ujianId)),
            ],
            'nama_peserta' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nomor_peserta.required' => 'Nomor peserta wajib diisi.',
            'nomor_peserta.unique' => 'Nomor peserta sudah digunakan pada ujian ini.',
            'nama_peserta.required' => 'Nama peserta wajib diisi.',
        ];
    }
}
