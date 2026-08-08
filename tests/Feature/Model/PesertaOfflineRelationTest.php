<?php

use App\Models\PesertaOffline;
use App\Models\Ujian;

describe('PesertaOffline relations', function () {
    it('belongs to an ujian', function () {
        $ujian = Ujian::factory()->create();
        $peserta = PesertaOffline::factory()->create(['ujian_id' => $ujian->id]);

        expect($peserta->ujian->id)->toBe($ujian->id);
    });

    it('exposes the inverse pesertaOffline relation on ujian', function () {
        $ujian = Ujian::factory()->create();
        PesertaOffline::factory()->count(3)->create(['ujian_id' => $ujian->id]);

        expect($ujian->pesertaOffline()->count())->toBe(3);
    });

    it('has a null ujianPeserta by default', function () {
        $peserta = PesertaOffline::factory()->create();

        expect($peserta->ujianPeserta)->toBeNull();
    });

    it('hides kode_akses from array serialization', function () {
        $peserta = PesertaOffline::factory()->create();

        expect($peserta->toArray())->not->toHaveKey('kode_akses');
    });
});
