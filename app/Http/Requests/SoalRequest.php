<?php

namespace App\Http\Requests;

use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use Illuminate\Foundation\Http\FormRequest;

/**
 * FORM REQUEST: SoalRequest
 * ==========================
 * Validasi paling KOMPLEKS di modul ini, karena aturannya BERBEDA-BEDA
 * tergantung pengaturan di SubJenisUjian yang dipilih (validasi kondisional).
 *
 * Dua faktor penentu:
 *   1. jumlah_jawaban_pilihan_ganda (4 atau 5) -> apakah opsi E wajib?
 *   2. sistem_penilaian:
 *        - "benar_salah"          -> butuh kunci_jawaban + nilai_bobot_benar
 *        - "tiap_jawaban_ada_poin" -> butuh nilai_bobot per opsi (A-D/E)
 */
class SoalRequest extends FormRequest
{
    /** Izinkan semua yang lolos middleware. */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * rules(): dibangun SECARA DINAMIS.
     * Kita intip dulu SubJenisUjian terkait, baru menentukan aturan mana yang berlaku.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Cari SubJenisUjian dari sub_indikator yang dipilih (lihat method di bawah).
        $subJenis = $this->resolveSubJenisUjian();
        // Apakah pakai 5 opsi (A-E)? Menentukan apakah opsi E & bobot E wajib.
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

        // ATURAN KONDISIONAL berdasarkan sistem penilaian:
        if ($subJenis && $subJenis->sistem_penilaian === 'benar_salah') {
            // Mode benar/salah: wajib pilih 1 kunci jawaban (A-D atau A-E).
            $rules['kunci_jawaban'] = ['required', 'in:'.($isFiveOptions ? 'A,B,C,D,E' : 'A,B,C,D')];
            $rules['nilai_bobot_benar'] = ['nullable', 'numeric', 'min:0'];
        } else {
            // Mode tiap jawaban ada poin: setiap opsi wajib punya nilai bobot.
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

    /**
     * resolveSubJenisUjian(): method bantuan untuk menemukan SubJenisUjian
     * dari sub_indikator yang dikirim form.
     *
     * Alur: sub_indikator_id -> cari SubIndikator -> ambil relasi subJenisUjian.
     * Dipakai di rules() (menentukan aturan) DAN dipanggil ulang di SoalController
     * (menentukan kolom bobot mana yang perlu dikosongkan saat update).
     * Bisa mengembalikan null jika sub_indikator tidak ditemukan.
     */
    public function resolveSubJenisUjian(): ?SubJenisUjian
    {
        $subIndikator = SubIndikator::find($this->sub_indikator_id);

        return $subIndikator?->subJenisUjian;
    }
}
