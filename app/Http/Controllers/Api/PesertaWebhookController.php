<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PesertaPembayaran;
use App\Services\Ujian\PesertaLanggananService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PesertaWebhookController extends Controller
{
    public function __construct(
        private readonly PesertaLanggananService $langganan
    ) {}

    public function xendit(Request $request): JsonResponse
    {
        $payload = $request->all();
        $externalId = $payload['external_id'] ?? null;
        $status = $payload['status'] ?? null;

        $pembayaran = $externalId ? PesertaPembayaran::where('nomor_pembayaran', $externalId)->first() : null;

        if (! $pembayaran) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if (in_array($status, ['PAID', 'SETTLED'], true)) {
            $this->langganan->markPaid($pembayaran, $payload['payment_method'] ?? null, $payload);

            return response()->json(['message' => 'Payment confirmed']);
        }

        if (in_array($status, ['EXPIRED', 'FAILED'], true)) {
            $this->langganan->markFailed(
                $pembayaran,
                $status === 'EXPIRED' ? PesertaPembayaran::STATUS_EXPIRED : PesertaPembayaran::STATUS_FAILED,
                $payload
            );

            return response()->json(['message' => 'Payment failed']);
        }

        return response()->json(['message' => 'Status unchanged']);
    }

    public function midtrans(Request $request): JsonResponse
    {
        $payload = $request->all();
        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        $pembayaran = $orderId ? PesertaPembayaran::where('nomor_pembayaran', $orderId)->first() : null;

        if (! $pembayaran) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if (in_array($transactionStatus, ['settlement', 'capture'], true)) {
            $this->langganan->markPaid($pembayaran, $payload['payment_type'] ?? null, $payload);

            return response()->json(['message' => 'Payment confirmed']);
        }

        if (in_array($transactionStatus, ['deny', 'cancel', 'expire'], true)) {
            $this->langganan->markFailed(
                $pembayaran,
                $transactionStatus === 'expire' ? PesertaPembayaran::STATUS_EXPIRED : PesertaPembayaran::STATUS_FAILED,
                $payload
            );

            return response()->json(['message' => 'Payment failed']);
        }

        return response()->json(['message' => 'Status unchanged']);
    }
}
