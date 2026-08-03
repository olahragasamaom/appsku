<?php

namespace App\Http\Requests;

use App\Models\SubJenisUjian;
use Illuminate\Foundation\Http\FormRequest;

class SubIndikatorRequest extends FormRequest
{
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
            'sub_jenis_ujian_id' => ['required', 'exists:panritta_sub_jenis_ujian,id'],
            'nama_sub_indikator' => ['required', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('sub_jenis_ujian_id')) {
            $subJenis = SubJenisUjian::find($this->sub_jenis_ujian_id);

            if ($subJenis) {
                $this->merge(['jenis_ujian_id' => $subJenis->jenis_ujian_id]);
            }
        }
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sub_jenis_ujian_id.required' => 'Sub jenis ujian wajib dipilih.',
            'sub_jenis_ujian_id.exists' => 'Sub jenis ujian tidak valid.',
            'nama_sub_indikator.required' => 'Nama sub indikator wajib diisi.',
            'nama_sub_indikator.max' => 'Nama sub indikator maksimal 255 karakter.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedWithJenisUjian(): array
    {
        $data = $this->validated();
        $subJenis = SubJenisUjian::find($data['sub_jenis_ujian_id']);
        $data['jenis_ujian_id'] = $subJenis?->jenis_ujian_id;

        return $data;
    }
}
