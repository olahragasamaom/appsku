<?php

use App\Models\Ujian;
use App\Services\Ujian\TokenService;

function tokenService(): TokenService
{
    return app(TokenService::class);
}

describe('TokenService::ensureToken', function () {
    it('generates a token when token_ujian is null', function () {
        $ujian = Ujian::factory()->create(['token_ujian' => null]);

        $token = tokenService()->ensureToken($ujian);

        expect($token)->toBeString();
        expect(strlen($token))->toBe(6);
        expect($ujian->fresh()->token_ujian)->toBe($token);
    });

    it('returns the existing token without regenerating', function () {
        $ujian = Ujian::factory()->create(['token_ujian' => 'ABCDEF']);

        $token = tokenService()->ensureToken($ujian);

        expect($token)->toBe('ABCDEF');
        expect($ujian->fresh()->token_ujian)->toBe('ABCDEF');
    });

    it('generates a token unique across offline_kelas exams', function () {
        $existingToken = 'ZZZZZZ';
        Ujian::factory()->create([
            'tipe_ujian' => 'offline_kelas',
            'token_ujian' => $existingToken,
        ]);

        $ujian = Ujian::factory()->create(['token_ujian' => null]);

        $token = tokenService()->ensureToken($ujian);

        expect($token)->not->toBe($existingToken);
    });
});
