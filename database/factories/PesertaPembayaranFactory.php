<?php

namespace Database\Factories;

use App\Models\Paket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PesertaPembayaranFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'paket_id' => Paket::factory(),
            'nomor_pembayaran' => 'UJN'.now()->format('Ymd').str_pad((string) $this->faker->unique()->randomNumber(4), 4, '0', STR_PAD_LEFT),
            'jumlah' => $this->faker->numberBetween(99000, 399000),
            'status' => 'pending',
            'gateway' => null,
            'gateway_reference' => null,
            'invoice_url' => null,
            'metode_pembayaran' => null,
            'gateway_response' => null,
            'dibayar_pada' => null,
            'kedaluwarsa_pada' => null,
            'bukti_pembayaran' => null,
            'catatan_peserta' => null,
            'bukti_pembayaran_verified_by' => null,
            'catatan_verifikasi' => null,
            'diverifikasi_pada' => null,
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function success(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'success',
            'dibayar_pada' => now(),
            'diverifikasi_pada' => now(),
            'bukti_pembayaran_verified_by' => 'admin@example.com',
        ]);
    }

    public function failed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'diverifikasi_pada' => now(),
            'bukti_pembayaran_verified_by' => 'admin@example.com',
            'catatan_verifikasi' => 'Nominal tidak sesuai',
        ]);
    }
}
