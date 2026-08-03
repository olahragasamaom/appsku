<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JenisUjianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $jenisUjianId = $this->route('jenisUjian')?->id;

        return [
            'nama_jenis_ujian' => [
                'required',
                'string',
                'max:100',
                Rule::unique('panritta_jenis_ujian', 'nama_jenis_ujian')->ignore($jenisUjianId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_jenis_ujian.required' => 'Jenis ujian wajib diisi.',
            'nama_jenis_ujian.max' => 'Jenis ujian maksimal 100 karakter.',
            'nama_jenis_ujian.unique' => 'Jenis ujian sudah digunakan.',
        ];
    }
}
