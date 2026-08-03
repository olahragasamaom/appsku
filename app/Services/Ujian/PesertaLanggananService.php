<?php

namespace App\Services\Ujian;

use App\Models\Paket;
use App\Models\PesertaLangganan;
use App\Models\PesertaPembayaran;
use App\Models\User;
use App\Services\PaymentGatewayService;
use Illuminate\Support\Facades\DB;

class PesertaLanggananService
{
    public function __construct(
        private readonly PaymentGatewayService $gateway
    ) {}

    /**
     * Subscribe a peserta to a paket. Free paket activates immediately;
     * paid paket creates a pending payment and gateway invoice.
     *
     * @return array{langganan: PesertaLangganan, pembayaran: PesertaPembayaran|null, invoice_url: string|null}
     */
    public function subscribe(User $user, Paket $paket): array
    {
        return DB::transaction(function () use ($user, $paket) {
            $langganan = PesertaLangganan::create([
                'user_id' => $user->id,
                'paket_id' => $paket->id,
                'status' => $paket->isGratis() ? 'active' : 'pending',
                'mulai_pada' => $paket->isGratis() ? now() : null,
                'berakhir_pada' => $paket->isGratis() ? now()->addDays($paket->durasi_hari) : null,
                'sisa_kuota_ujian' => $paket->kuota_ujian,
            ]);

            if ($paket->isGratis()) {
                return ['langganan' => $langganan, 'pembayaran' => null, 'invoice_url' => null];
            }

            $pembayaran = PesertaPembayaran::create([
                'user_id' => $user->id,
                'paket_id' => $paket->id,
                'langganan_id' => $langganan->id,
                'jumlah' => $paket->harga,
                'status' => PesertaPembayaran::STATUS_PENDING,
                'kedaluwarsa_pada' => now()->addDay(),
            ]);

            $invoiceUrl = $this->createGatewayInvoice($user, $paket, $pembayaran);

            return ['langganan' => $langganan, 'pembayaran' => $pembayaran, 'invoice_url' => $invoiceUrl];
        });
    }

    private function createGatewayInvoice(User $user, Paket $paket, PesertaPembayaran $pembayaran): ?string
    {
        try {
            $result = $this->gateway->createTransaction([
                'order_id' => $pembayaran->nomor_pembayaran,
                'amount' => (float) $paket->harga,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'description' => 'Langganan Paket '.$paket->nama_paket,
            ]);

            $reference = $result['invoice_id'] ?? $result['token'] ?? null;
            $invoiceUrl = $result['invoice_url'] ?? $result['redirect_url'] ?? null;

            $pembayaran->update([
                'gateway' => $this->gateway->activeGatewayName(),
                'gateway_reference' => $reference,
                'invoice_url' => $invoiceUrl,
                'gateway_response' => $result,
            ]);

            return $invoiceUrl;
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Mark a payment as successful and activate its subscription (idempotent).
     */
    public function markPaid(PesertaPembayaran $pembayaran, ?string $metode = null, array $response = []): void
    {
        if ($pembayaran->isSuccess()) {
            return;
        }

        DB::transaction(function () use ($pembayaran, $metode, $response) {
            $pembayaran->update([
                'status' => PesertaPembayaran::STATUS_SUCCESS,
                'metode_pembayaran' => $metode,
                'gateway_response' => array_merge((array) $pembayaran->gateway_response, $response),
                'dibayar_pada' => now(),
            ]);

            $langganan = $pembayaran->langganan;

            if ($langganan) {
                $paket = $langganan->paket;

                $langganan->update([
                    'status' => 'active',
                    'mulai_pada' => $langganan->mulai_pada ?? now(),
                    'berakhir_pada' => now()->addDays($paket->durasi_hari),
                    'sisa_kuota_ujian' => $paket->kuota_ujian,
                ]);
            }
        });
    }

    public function markFailed(PesertaPembayaran $pembayaran, string $status = PesertaPembayaran::STATUS_FAILED, array $response = []): void
    {
        if ($pembayaran->isSuccess()) {
            return;
        }

        $pembayaran->update([
            'status' => $status,
            'gateway_response' => array_merge((array) $pembayaran->gateway_response, $response),
        ]);

        $pembayaran->langganan?->update(['status' => 'cancelled']);
    }
}
