<?php

namespace App\Http\Requests;

use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use Illuminate\Foundation\Http\FormRequest;

class SoalRequest extends FormRequest
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
        $subJenis = $this->resolveSubJenisUjian();
        $isFiveOptions = $subJenis && (int) $subJenis->jumlah_jawaban_pilihan_ganda === 5;

        $rules = [
            'sub_indikator_id' => ['required', 'exists:panritta_sub_indikator,id'],
            'soal' => ['required', 'string'],
            'gambar_soal' => ['nullable', 'image', 'max:2048'],
            'opsi_a' => ['required', 'string'],
            'opsi_b' => ['required', 'string'],
            'opsi_c' => ['required', 'string'],
            'opsi_d' => ['required', 'string'],
            'opsi_e' => [$isFiveOptions ? 'required' : 'nullable', 'string'],
            'gambar_opsi_a' => ['nullable', 'image', 'max:2048'],
            'gambar_opsi_b' => ['nullable', 'image', 'max:2048'],
            'gambar_opsi_c' => ['nullable', 'image', 'max:2048'],
            'gambar_opsi_d' => ['nullable', 'image', 'max:2048'],
            'gambar_opsi_e' => ['nullable', 'image', 'max:2048'],
            'pembahasan' => ['nullable', 'string'],
            'gambar_pembahasan' => ['nullable', 'image', 'max:2048'],
        ];

        if ($subJenis && $subJenis->sistem_penilaian === 'benar_salah') {
            $rules['kunci_jawaban'] = ['required', 'in:'.($isFiveOptions ? 'A,B,C,D,E' : 'A,B,C,D')];
            $rules['nilai_bobot_benar'] = ['nullable', 'numeric', 'min:0'];
        } else {
            $rules['nilai_bobot_a'] = ['required', 'numeric', 'min:0'];
            $rules['nilai_bobot_b'] = ['required', 'numeric', 'min:0'];
            $rules['nilai_bobot_c'] = ['required', 'numeric', 'min:0'];
            $rules['nilai_bobot_d'] = ['required', 'numeric', 'min:0'];

            if ($isFiveOptions) {
                $rules['nilai_bobot_e'] = ['required', 'numeric', 'min:0'];
            }
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sub_indikator_id.required' => 'Sub indikator wajib dipilih.',
            'sub_indikator_id.exists' => 'Sub indikator tidak valid.',
            'soal.required' => 'Teks soal wajib diisi.',
            'opsi_a.required' => 'Opsi A wajib diisi.',
            'opsi_b.required' => 'Opsi B wajib diisi.',
            'opsi_c.required' => 'Opsi C wajib diisi.',
            'opsi_d.required' => 'Opsi D wajib diisi.',
            'opsi_e.required' => 'Opsi E wajib diisi.',
            'kunci_jawaban.required' => 'Kunci jawaban wajib dipilih.',
            'kunci_jawaban.in' => 'Kunci jawaban tidak valid.',
            'nilai_bobot_a.required' => 'Nilai bobot A wajib diisi.',
            'nilai_bobot_b.required' => 'Nilai bobot B wajib diisi.',
            'nilai_bobot_c.required' => 'Nilai bobot C wajib diisi.',
            'nilai_bobot_d.required' => 'Nilai bobot D wajib diisi.',
            'nilai_bobot_e.required' => 'Nilai bobot E wajib diisi.',
        ];
    }

    public function resolveSubJenisUjian(): ?SubJenisUjian
    {
        $subIndikator = SubIndikator::find($this->sub_indikator_id);

        return $subIndikator?->subJenisUjian;
    }
}
