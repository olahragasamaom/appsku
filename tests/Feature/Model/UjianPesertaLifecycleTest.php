<?php

use App\Models\Paket;
use App\Models\PesertaLangganan;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\User;

describe('UjianPeserta lifecycle fields', function () {
    it('mass-assigns and casts the lifecycle fields', function () {
        $langganan = PesertaLangganan::create([
            'user_id' => User::factory()->create()->id,
            'paket_id' => Paket::factory()->create()->id,
            'status' => 'active',
        ]);

        $deadline = now()->addHour();

        $peserta = UjianPeserta::create([
            'ujian_id' => Ujian::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'langganan_id' => $langganan->id,
            'status' => 'sedang_ujian',
            'batas_waktu' => $deadline,
            'auto_submitted' => true,
        ]);

        $fresh = $peserta->fresh();

        expect($fresh->langganan_id)->toBe($langganan->id);
        expect($fresh->batas_waktu)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
        expect($fresh->batas_waktu->toDateTimeString())->toBe($deadline->toDateTimeString());
        expect($fresh->auto_submitted)->toBeTrue();
    });

    it('defaults auto_submitted to false', function () {
        $peserta = UjianPeserta::create([
            'ujian_id' => Ujian::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'sedang_ujian',
        ]);

        expect($peserta->fresh()->auto_submitted)->toBeFalse();
    });
});
