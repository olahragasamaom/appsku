<?php

use App\Models\Paket;
use App\Models\Ujian;
use App\Services\Ujian\PaketUjianSyncService;
use Illuminate\Validation\ValidationException;

function syncService(): PaketUjianSyncService
{
    return app(PaketUjianSyncService::class);
}

describe('PaketUjianSyncService::sync', function () {
    it('attaches online exams to a paket via pivot', function () {
        $paket = Paket::factory()->create();
        $ujianA = Ujian::factory()->online()->create();
        $ujianB = Ujian::factory()->online()->create();

        syncService()->sync($paket, [$ujianA->id, $ujianB->id]);

        expect($paket->ujians()->count())->toBe(2);
    });

    it('rejects offline exams on sync', function () {
        $paket = Paket::factory()->create();
        $offline = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        expect(fn () => syncService()->sync($paket, [$offline->id]))
            ->toThrow(ValidationException::class);

        expect($paket->ujians()->count())->toBe(0);
    });

    it('removes detached exams on sync', function () {
        $paket = Paket::factory()->create();
        $ujianA = Ujian::factory()->online()->create();
        $ujianB = Ujian::factory()->online()->create();

        syncService()->sync($paket, [$ujianA->id, $ujianB->id]);
        syncService()->sync($paket, [$ujianA->id]);

        expect($paket->ujians()->pluck('panritta_ujian.id')->all())->toBe([$ujianA->id]);
    });

    it('leaves akses_member column untouched (CF-2)', function () {
        $paket = Paket::factory()->create();
        $ujian = Ujian::factory()->online()->create([
            'akses_member' => ['Free', 'Basic'],
        ]);

        syncService()->sync($paket, [$ujian->id]);

        expect($ujian->fresh()->akses_member)->toBe(['Free', 'Basic']);
    });
});

describe('PaketUjianSyncService::attach', function () {
    it('attaches an online exam to a paket', function () {
        $paket = Paket::factory()->create();
        $ujian = Ujian::factory()->online()->create();

        syncService()->attach($paket, $ujian);

        expect($paket->ujians()->where('panritta_ujian.id', $ujian->id)->exists())->toBeTrue();
    });

    it('rejects an offline exam', function () {
        $paket = Paket::factory()->create();
        $ujian = Ujian::factory()->create(['tipe_ujian' => 'offline_kelas']);

        expect(fn () => syncService()->attach($paket, $ujian))
            ->toThrow(ValidationException::class);
    });

    it('is idempotent when attaching the same exam twice', function () {
        $paket = Paket::factory()->create();
        $ujian = Ujian::factory()->online()->create();

        syncService()->attach($paket, $ujian);
        syncService()->attach($paket, $ujian);

        expect($paket->ujians()->count())->toBe(1);
    });
});

describe('PaketUjianSyncService::detach', function () {
    it('detaches an exam from a paket', function () {
        $paket = Paket::factory()->create();
        $ujian = Ujian::factory()->online()->create();

        syncService()->attach($paket, $ujian);
        syncService()->detach($paket, $ujian);

        expect($paket->ujians()->count())->toBe(0);
    });
});
