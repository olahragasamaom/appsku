<?php

use App\Models\Paket;
use App\Models\User;
use App\Services\PaymentGatewayService;
use App\Services\Ujian\PesertaLanggananService;

function langgananService(): PesertaLanggananService
{
    return app(PesertaLanggananService::class);
}

describe('PesertaLanggananService — unlimited quota (M-AU-4)', function () {
    it('sets sisa_kuota_ujian to null when paket kuota_ujian is null (subscribe — free paket)', function () {
        $paket = Paket::factory()->create(['harga' => 0, 'kuota_ujian' => null]);
        $user = User::factory()->create(['is_peserta' => true]);

        $result = langgananService()->subscribe($user, $paket);

        expect($result['langganan']->sisa_kuota_ujian)->toBeNull();
    });

    it('sets sisa_kuota_ujian to null when paket kuota_ujian is null (markPaid)', function () {
        $gateway = Mockery::mock(PaymentGatewayService::class);
        $gateway->shouldReceive('createTransaction')->andReturn([
            'invoice_url' => 'https://example.com/pay',
            'invoice_id' => 'INV-001',
        ]);
        $gateway->shouldReceive('activeGatewayName')->andReturn('midtrans');
        app()->instance(PaymentGatewayService::class, $gateway);

        $paket = Paket::factory()->create(['harga' => 50000, 'kuota_ujian' => null]);
        $user = User::factory()->create(['is_peserta' => true]);

        $result = langgananService()->subscribe($user, $paket);

        $pembayaran = $result['pembayaran'];

        langgananService()->markPaid($pembayaran, 'transfer');

        $langganan = $result['langganan']->fresh();

        expect($langganan->status)->toBe('active');
        expect($langganan->sisa_kuota_ujian)->toBeNull();
    });

    it('sets sisa_kuota_ujian to the paket kuota when not null (subscribe)', function () {
        $paket = Paket::factory()->create(['harga' => 0, 'kuota_ujian' => 10]);
        $user = User::factory()->create(['is_peserta' => true]);

        $result = langgananService()->subscribe($user, $paket);

        expect($result['langganan']->sisa_kuota_ujian)->toBe(10);
    });
});
