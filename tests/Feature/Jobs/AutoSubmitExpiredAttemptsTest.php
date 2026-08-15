<?php

use App\Jobs\AutoSubmitExpiredAttempts;
use App\Models\Ujian;
use App\Models\User;

describe('AutoSubmitExpiredAttempts job', function () {
    it('finalizes an attempt past its snapshot deadline', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $user = User::factory()->create();

        $expired = $ujian->peserta()->create([
            'user_id' => $user->id,
            'status' => 'sedang_ujian',
            'batas_waktu' => now()->subMinute(),
        ]);

        (new AutoSubmitExpiredAttempts)->handle(app(App\Services\Ujian\AttemptService::class));

        $fresh = $expired->fresh();
        expect($fresh->status)->toBe('selesai');
        expect($fresh->auto_submitted)->toBeTrue();
    });

    it('leaves non-expired attempts untouched', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $user = User::factory()->create();

        $active = $ujian->peserta()->create([
            'user_id' => $user->id,
            'status' => 'sedang_ujian',
            'batas_waktu' => now()->addHour(),
        ]);

        (new AutoSubmitExpiredAttempts)->handle(app(App\Services\Ujian\AttemptService::class));

        expect($active->fresh()->status)->toBe('sedang_ujian');
    });
});
