<?php

namespace App\Http\Requests;

use App\Models\SubJenisUjian;
use Illuminate\Foundation\Http\FormRequest;

/**
 * FORM REQUEST: SubIndikatorRequest
 * ==================================
 * Validasi untuk Sub Indikator. File ini memperkenalkan 2 konsep tambahan:
 *   1. prepareForValidation() -> mengutak-atik input SEBELUM divalidasi.
 *   2. validatedWithJenisUjian() -> method bantuan buatan sendiri yang
 *      dipanggil controller untuk mendapatkan data siap-simpan.
 *
 * KONTEKS: form hanya meminta user memilih SubJenisUjian. Tapi tabel
 * sub_indikator juga butuh jenis_ujian_id (jalan pintas ke kakek).
 * Nilai itu TIDAK dari form -> kita cari & isi sendiri di sini.
 */
class SubIndikatorRequest extends FormRequest
{
    /** Izinkan semua yang lolos middleware. */
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

    /**
     * prepareForValidation(): JALAN OTOMATIS sebelum rules() dijalankan.
     * Berguna untuk membersihkan atau menambah data input.
     *
     * Di sini: jika user sudah memilih sub_jenis_ujian_id, kita cari record-nya,
     * ambil jenis_ujian_id miliknya, lalu "sisipkan" ke input via merge().
     * Hasilnya jenis_ujian_id ikut tersedia tanpa perlu field di form.
     */
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
     * validatedWithJenisUjian(): METHOD BANTUAN buatan sendiri (bukan bawaan Laravel).
     * Dipanggil controller (store/update) sebagai pengganti $request->validated().
     *
     * Alurnya:
     *   1. Ambil data yang lolos validasi ($this->validated()).
     *   2. Cari SubJenisUjian yang dipilih, lalu tambahkan jenis_ujian_id-nya
     *      ke dalam array data.
     *   3. Kembalikan array lengkap yang siap dipakai ->create()/->update().
     *
     * Kenapa dibuat method sendiri? Agar logika "isi jenis_ujian_id otomatis"
     * terpusat di satu tempat dan controller tetap ringkas.
     *
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
