<?php

use App\Models\Paket;
use App\Models\Ujian;

describe('Paket <-> Ujian pivot relation', function () {
    it('attaches an ujian to a paket', function () {
        $paket = Paket::factory()->create();
        $ujian = Ujian::factory()->online()->create();

        $paket->ujians()->attach($ujian->id);

        expect($paket->ujians()->pluck('panritta_ujian.id')->all())->toBe([$ujian->id]);
    });

    it('exposes the inverse relation from ujian', function () {
        $paket = Paket::factory()->create();
        $ujian = Ujian::factory()->online()->create();

        $paket->ujians()->attach($ujian->id);

        expect($ujian->pakets()->pluck('panritta_paket.id')->all())->toBe([$paket->id]);
    });

    it('detaches an ujian from a paket', function () {
        $paket = Paket::factory()->create();
        $ujian = Ujian::factory()->online()->create();

        $paket->ujians()->attach($ujian->id);
        $paket->ujians()->detach($ujian->id);

        expect($paket->ujians()->count())->toBe(0);
    });

    it('supports many ujians per paket', function () {
        $paket = Paket::factory()->create();
        $ujianA = Ujian::factory()->online()->create();
        $ujianB = Ujian::factory()->online()->create();

        $paket->ujians()->attach([$ujianA->id, $ujianB->id]);

        expect($paket->ujians()->count())->toBe(2);
    });
});
