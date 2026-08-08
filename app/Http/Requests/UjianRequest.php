<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UjianRequest extends FormRequest
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
            'nama_ujian' => ['required', 'string', 'max:255'],
            'tipe_ujian' => ['required', Rule::in(['offline_kelas', 'online_paket'])],
            'sub_jenis_ujian_id' => ['nullable', 'required_if:tipe_ujian,online_paket', 'prohibited_if:tipe_ujian,offline_kelas', Rule::exists('panritta_sub_jenis_ujian', 'id')],
            'jumlah_soal' => ['required', 'integer', 'min:0', 'max:65535'],
            'acak_soal' => ['sometimes', 'boolean'],
            'tampilkan_hasil' => ['sometimes', 'boolean'],
            'status' => ['sometimes', Rule::in(['draft', 'aktif', 'selesai'])],

            'jenis_ujian_id' => ['required', 'array', 'min:1'],
            'jenis_ujian_id.*' => ['integer', Rule::exists('panritta_jenis_ujian', 'id')],
            'passing_grade' => ['sometimes', 'array'],
            'passing_grade.*' => ['nullable', 'numeric', 'min:0'],

            'tanggal_ujian' => ['nullable', 'required_if:tipe_ujian,offline_kelas', 'date'],
            'durasi_ujian' => ['nullable', 'required_if:tipe_ujian,offline_kelas', 'integer', 'min:1'],
            'batas_keterlambatan' => ['nullable', 'date', 'after_or_equal:tanggal_ujian'],
            'token_ujian' => ['nullable', 'string', 'max:50'],

            'akses_member' => ['nullable', 'required_if:tipe_ujian,online_paket', 'array'],
            'akses_member.*' => ['string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_ujian.required' => 'Nama ujian wajib diisi.',
            'jenis_ujian_id.required' => 'Pilih minimal satu jenis ujian.',
            'jenis_ujian_id.min' => 'Pilih minimal satu jenis ujian.',
            'tanggal_ujian.required_if' => 'Tanggal ujian wajib diisi untuk ujian offline.',
            'durasi_ujian.required_if' => 'Durasi ujian wajib diisi untuk ujian offline.',
            'batas_keterlambatan.after_or_equal' => 'Batas keterlambatan tidak boleh sebelum tanggal ujian.',
            'akses_member.required_if' => 'Pilih minimal satu akses member untuk ujian online.',
            'sub_jenis_ujian_id.required_if' => 'Sub jenis ujian wajib dipilih untuk ujian online.',
            'sub_jenis_ujian_id.prohibited_if' => 'Sub jenis ujian tidak boleh diisi untuk ujian offline.',
        ];
    }
}
