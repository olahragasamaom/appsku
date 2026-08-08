<?php

use App\Models\Paket;
use App\Models\PesertaLangganan;
use App\Models\PesertaOffline;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\User;
use App\Services\Ujian\AttemptService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

function attemptService(): AttemptService
{
    return app(AttemptService::class);
}

function makePaket(array $attributes = []): Paket
{
    return Paket::factory()->create(array_merge(['harga' => 0, 'durasi_hari' => 30], $attributes));
}

function makeActiveLangganan(User $user, Paket $paket, array $attributes = []): PesertaLangganan
{
    return PesertaLangganan::create(array_merge([
        'user_id' => $user->id,
        'paket_id' => $paket->id,
        'status' => 'active',
        'mulai_pada' => now(),
        'berakhir_pada' => now()->addDays(30),
        'sisa_kuota_ujian' => $paket->kuota_ujian,
    ], $attributes));
}

describe('start — offline', function () {
    it('creates an attempt with a snapshot batas_waktu of now + durasi', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'durasi_ujian' => 90, 'status' => 'aktif']);
        $user = User::factory()->create();

        $attempt = attemptService()->start($ujian, $user->id);

        expect($attempt->status)->toBe('sedang_ujian');
        expect($attempt->batas_waktu)->not->toBeNull();
        expect($attempt->batas_waktu->isFuture())->toBeTrue();
        expect(now()->addMinutes(89)->isBefore($attempt->batas_waktu))->toBeTrue();
    });
});

describe('start — online', function () {
    it('snapshots the subscription berakhir_pada as the deadline (C-AU-1)', function () {
        $paket = makePaket(['kuota_ujian' => 5]);
        $ujian = Ujian::factory()->online()->create(['status' => 'aktif']);
        $ujian->pakets()->attach($paket->id);

        $user = User::factory()->create();
        $berakhirPada = now()->addDays(30);
        makeActiveLangganan($user, $paket, ['berakhir_pada' => $berakhirPada, 'sisa_kuota_ujian' => 5]);

        $attempt = attemptService()->start($ujian, $user->id);

        expect($attempt->batas_waktu->toDateTimeString())->toBe($berakhirPada->toDateTimeString());
    });

    it('decrements sisa_kuota_ujian on start', function () {
        $paket = makePaket(['kuota_ujian' => 3]);
        $ujian = Ujian::factory()->online()->create(['status' => 'aktif']);
        $ujian->pakets()->attach($paket->id);

        $user = User::factory()->create();
        $langganan = makeActiveLangganan($user, $paket, ['sisa_kuota_ujian' => 3]);

        attemptService()->start($ujian, $user->id);

        expect($langganan->fresh()->sisa_kuota_ujian)->toBe(2);
    });

    it('rejects start when quota is exhausted', function () {
        $paket = makePaket(['kuota_ujian' => 1]);
        $ujian = Ujian::factory()->online()->create(['status' => 'aktif']);
        $ujian->pakets()->attach($paket->id);

        $user = User::factory()->create();
        makeActiveLangganan($user, $paket, ['sisa_kuota_ujian' => 0]);

        expect(fn () => attemptService()->start($ujian, $user->id))
            ->toThrow(ValidationException::class);
    });

    it('allows unlimited attempts when kuota_ujian is null (M-AU-4)', function () {
        $paket = makePaket(['kuota_ujian' => null]);
        $ujian = Ujian::factory()->online()->create(['status' => 'aktif']);
        $ujian->pakets()->attach($paket->id);

        $user = User::factory()->create();
        makeActiveLangganan($user, $paket, ['sisa_kuota_ujian' => null]);

        $first = attemptService()->start($ujian, $user->id);
        $first->forceFill(['status' => 'selesai'])->save();

        $second = attemptService()->start($ujian, $user->id);

        expect($second->id)->not->toBe($first->id);
    });

    it('rejects start without an active subscription', function () {
        $paket = makePaket(['kuota_ujian' => 5]);
        $ujian = Ujian::factory()->online()->create(['status' => 'aktif']);
        $ujian->pakets()->attach($paket->id);

        $user = User::factory()->create();

        expect(fn () => attemptService()->start($ujian, $user->id))
            ->toThrow(ValidationException::class);
    });
});

describe('re-take creates a new row (AD-9)', function () {
    it('inserts a new UjianPeserta row instead of reusing the finished one', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'durasi_ujian' => 90, 'status' => 'aktif']);
        $user = User::factory()->create();

        $first = attemptService()->start($ujian, $user->id);
        $first->forceFill(['status' => 'selesai'])->save();

        $second = attemptService()->start($ujian, $user->id);

        expect($second->id)->not->toBe($first->id);
        expect(UjianPeserta::where('ujian_id', $ujian->id)->where('user_id', $user->id)->count())->toBe(2);
    });
});

describe('startOffline', function () {
    it('creates an attempt and links it to the peserta offline record', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'durasi_ujian' => 90, 'status' => 'aktif']);
        $plaintext = 'rahasia123';

        $pesertaOffline = PesertaOffline::factory()->create([
            'ujian_id' => $ujian->id,
            'kode_akses' => Hash::make($plaintext),
        ]);

        $attempt = attemptService()->startOffline($pesertaOffline->nomor_peserta, $plaintext, $ujian);

        expect($attempt->status)->toBe('sedang_ujian');
        expect($pesertaOffline->fresh()->ujian_peserta_id)->toBe($attempt->id);
    });

    it('rejects wrong kode_akses', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'durasi_ujian' => 90, 'status' => 'aktif']);
        $pesertaOffline = PesertaOffline::factory()->create([
            'ujian_id' => $ujian->id,
            'kode_akses' => Hash::make('benar'),
        ]);

        expect(fn () => attemptService()->startOffline($pesertaOffline->nomor_peserta, 'salah', $ujian))
            ->toThrow(ValidationException::class);
    });
});

describe('submit', function () {
    it('is a no-op when the attempt is already selesai', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $user = User::factory()->create();

        $attempt = $ujian->peserta()->create([
            'user_id' => $user->id,
            'status' => 'selesai',
            'waktu_selesai' => now(),
        ]);

        $before = $attempt->updated_at;
        attemptService()->submit($attempt);

        expect($attempt->fresh()->updated_at->toDateTimeString())->toBe($before->toDateTimeString());
    });
});

describe('autoSubmitExpired', function () {
    it('finalizes expired attempts and sets auto_submitted = true', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $user = User::factory()->create();

        $expired = $ujian->peserta()->create([
            'user_id' => $user->id,
            'status' => 'sedang_ujian',
            'batas_waktu' => now()->subMinute(),
        ]);

        attemptService()->autoSubmitExpired();

        $fresh = $expired->fresh();
        expect($fresh->status)->toBe('selesai');
        expect($fresh->auto_submitted)->toBeTrue();
    });

    it('does not touch non-expired active attempts', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $user = User::factory()->create();

        $active = $ujian->peserta()->create([
            'user_id' => $user->id,
            'status' => 'sedang_ujian',
            'batas_waktu' => now()->addHour(),
        ]);

        attemptService()->autoSubmitExpired();

        expect($active->fresh()->status)->toBe('sedang_ujian');
    });

    it('skips attempts already selesai', function () {
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas', 'status' => 'aktif']);
        $user = User::factory()->create();

        $done = $ujian->peserta()->create([
            'user_id' => $user->id,
            'status' => 'selesai',
            'batas_waktu' => now()->subHour(),
        ]);

        attemptService()->autoSubmitExpired();

        expect($done->fresh()->auto_submitted)->toBeFalse();
    });
});
