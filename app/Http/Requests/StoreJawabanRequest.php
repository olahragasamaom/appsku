<?php

namespace App\Http\Requests;

use App\Models\Ujian;
use App\Models\UjianPeserta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * FORM REQUEST: StoreJawabanRequest
 * =================================
 * Validasi penyimpanan satu jawaban peserta saat ujian berlangsung.
 *
 * Aturan kunci (M-AU-8 / AD-10):
 *   - Jawaban harus salah satu dari A-E, atau null (soal dikosongkan).
 *   - Attempt milik peserta harus berstatus 'sedang_ujian'.
 *   - Batas waktu dibaca dari SNAPSHOT `panritta_ujian_peserta.batas_waktu`,
 *     bukan dari langganan/`durasi_ujian` yang bisa berubah di tengah pengerjaan.
 */
class StoreJawabanRequest extends FormRequest
{
    /** Peserta yang sedang mengerjakan (di-resolve sekali). */
    private ?UjianPeserta $peserta = null;

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
            'ujian_soal_id' => ['required', 'integer'],
            'jawaban' => ['nullable', 'in:A,B,C,D,E'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ujian_soal_id.required' => 'Soal wajib ditentukan.',
            'jawaban.in' => 'Jawaban tidak valid.',
        ];
    }

    /**
     * Validasi tambahan: status attempt & batas waktu snapshot (M-AU-8/AD-10).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $peserta = $this->resolvePeserta();

            if (! $peserta) {
                throw ValidationException::withMessages([
                    'attempt' => 'Sesi ujian tidak ditemukan.',
                ]);
            }

            if ($peserta->status !== 'sedang_ujian') {
                throw ValidationException::withMessages([
                    'attempt' => 'Ujian sudah tidak berlangsung.',
                ]);
            }

            if ($peserta->batas_waktu && now()->greaterThanOrEqualTo($peserta->batas_waktu)) {
                throw ValidationException::withMessages([
                    'attempt' => 'Waktu ujian sudah habis.',
                ]);
            }
        });
    }

    /**
     * Peserta attempt untuk request ini (jalur offline via session, atau online via auth).
     */
    public function resolvePeserta(): ?UjianPeserta
    {
        if ($this->peserta !== null) {
            return $this->peserta;
        }

        /** @var Ujian $ujian */
        $ujian = $this->route('ujian');

        if ($this->session()->has('offline_attempt_id')) {
            $sessionUjianId = $this->session()->get('offline_ujian_id');

            if ($sessionUjianId !== $ujian->id) {
                return null;
            }

            return $this->peserta = $ujian->peserta()
                ->where('id', $this->session()->get('offline_attempt_id'))
                ->first();
        }

        if (! $this->user()) {
            return null;
        }

        return $this->peserta = $ujian->peserta()
            ->where('user_id', $this->user()->id)
            ->first();
    }
}
