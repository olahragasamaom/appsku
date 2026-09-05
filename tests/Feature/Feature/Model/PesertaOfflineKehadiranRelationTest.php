<?php

use App\Models\PesertaOffline;
use App\Models\PesertaOfflineKehadiran;
use App\Models\Ujian;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PesertaOfflineKehadiran relationships', function () {
    it('belongs to peserta offline', function () {
        $pesertaOffline = PesertaOffline::factory()->create();
        $ujian = Ujian::factory()->offline()->create();

        $kehadiran = PesertaOfflineKehadiran::factory()->create([
            'peserta_offline_id' => $pesertaOffline->id,
            'ujian_id' => $ujian->id,
        ]);

        expect($kehadiran->pesertaOffline()->first()->id)->toBe($pesertaOffline->id);
        expect($kehadiran->pesertaOffline instanceof PesertaOffline)->toBeTrue();
    });

    it('belongs to ujian', function () {
        $pesertaOffline = PesertaOffline::factory()->create();
        $ujian = Ujian::factory()->offline()->create();

        $kehadiran = PesertaOfflineKehadiran::factory()->create([
            'peserta_offline_id' => $pesertaOffline->id,
            'ujian_id' => $ujian->id,
        ]);

        expect($kehadiran->ujian()->first()->id)->toBe($ujian->id);
        expect($kehadiran->ujian instanceof Ujian)->toBeTrue();
    });

    it('peserta offline has many kehadiran records', function () {
        $pesertaOffline = PesertaOffline::factory()->create();
        $ujian1 = Ujian::factory()->offline()->create();
        $ujian2 = Ujian::factory()->offline()->create();

        PesertaOfflineKehadiran::factory()->create([
            'peserta_offline_id' => $pesertaOffline->id,
            'ujian_id' => $ujian1->id,
        ]);

        PesertaOfflineKehadiran::factory()->create([
            'peserta_offline_id' => $pesertaOffline->id,
            'ujian_id' => $ujian2->id,
        ]);

        expect($pesertaOffline->kehadiran()->count())->toBe(2);
        expect($pesertaOffline->kehadiran->pluck('ujian_id')->all())->toContain($ujian1->id, $ujian2->id);
    });

    it('ujian has many kehadiran records', function () {
        $ujian = Ujian::factory()->offline()->create();
        $pesertaOffline1 = PesertaOffline::factory()->create(['ujian_id' => $ujian->id]);
        $pesertaOffline2 = PesertaOffline::factory()->create(['ujian_id' => $ujian->id]);

        PesertaOfflineKehadiran::factory()->create([
            'peserta_offline_id' => $pesertaOffline1->id,
            'ujian_id' => $ujian->id,
        ]);

        PesertaOfflineKehadiran::factory()->create([
            'peserta_offline_id' => $pesertaOffline2->id,
            'ujian_id' => $ujian->id,
        ]);

        expect($ujian->pesertaOfflineKehadiran()->count())->toBe(2);
    });

    it('has correct status_kehadiran default', function () {
        $pesertaOffline = PesertaOffline::factory()->create();
        $ujian = Ujian::factory()->offline()->create();

        $kehadiran = PesertaOfflineKehadiran::factory()->create([
            'peserta_offline_id' => $pesertaOffline->id,
            'ujian_id' => $ujian->id,
        ]);

        expect($kehadiran->status_kehadiran)->toBe('tidak_hadir');
    });
});
