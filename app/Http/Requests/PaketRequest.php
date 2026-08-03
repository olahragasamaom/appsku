<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PaketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->input('slug') ?: $this->input('nama_paket', '')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $paketId = $this->route('paket')?->id;

        return [
            'nama_paket' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('panritta_paket', 'slug')->ignore($paketId)],
            'deskripsi' => ['nullable', 'string'],
            'harga' => ['required', 'numeric', 'min:0'],
            'durasi_hari' => ['required', 'integer', 'min:1'],
            'kuota_ujian' => ['nullable', 'integer', 'min:0'],
            'video_pembahasan' => ['sometimes', 'boolean'],
            'analitik' => ['sometimes', 'boolean'],
            'sertifikat' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_paket.required' => 'Nama paket wajib diisi.',
            'harga.required' => 'Harga wajib diisi.',
            'durasi_hari.required' => 'Durasi paket wajib diisi.',
        ];
    }
}
