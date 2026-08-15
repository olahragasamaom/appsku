<?php

use App\Jobs\ExpireSubscriptions;
use App\Models\Paket;
use App\Models\PesertaLangganan;
use App\Models\User;

function makeLangganan(array $attributes = []): PesertaLangganan
{
    $paket = Paket::factory()->create(['harga' => 0, 'durasi_hari' => 30]);
    $user = User::factory()->create(['is_peserta' => true]);

    return PesertaLangganan::create(array_merge([
        'user_id' => $user->id,
        'paket_id' => $paket->id,
        'status' => 'active',
        'mulai_pada' => now()->subDays(31),
        'berakhir_pada' => now()->subDay(),
        'sisa_kuota_ujian' => null,
    ], $attributes));
}

describe('ExpireSubscriptions job', function () {
    it('flips an active subscription to expired when berakhir_pada has passed', function () {
        $langganan = makeLangganan(['berakhir_pada' => now()->subDay()]);

        (new ExpireSubscriptions)->handle();

        expect($langganan->fresh()->status)->toBe('expired');
    });

    it('leaves subscriptions within their active window untouched', function () {
        $langganan = makeLangganan(['berakhir_pada' => now()->addDays(10)]);

        (new ExpireSubscriptions)->handle();

        expect($langganan->fresh()->status)->toBe('active');
    });

    it('does not re-process non-active subscriptions', function () {
        $langganan = makeLangganan(['status' => 'cancelled', 'berakhir_pada' => now()->subDay()]);

        (new ExpireSubscriptions)->handle();

        expect($langganan->fresh()->status)->toBe('cancelled');
    });
});
