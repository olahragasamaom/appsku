<?php

namespace App\Services\Ujian;

use App\Models\Ujian;
use Illuminate\Support\Str;

class TokenService
{
    /**
     * Ensure the ujian has a token. If null, generate a unique one
     * across all offline_kelas exams and persist it.
     */
    public function ensureToken(Ujian $ujian): string
    {
        if ($ujian->token_ujian !== null) {
            return $ujian->token_ujian;
        }

        $token = $this->generateUniqueToken();

        $ujian->forceFill(['token_ujian' => $token])->save();

        return $token;
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = strtoupper(Str::random(6));
        } while (
            Ujian::query()
                ->where('tipe_ujian', 'offline_kelas')
                ->where('token_ujian', $token)
                ->exists()
        );

        return $token;
    }
}
