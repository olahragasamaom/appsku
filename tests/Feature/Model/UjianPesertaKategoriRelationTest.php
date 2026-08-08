<?php

use App\Models\JenisUjian;
use App\Models\Paket;
use App\Models\PesertaLangganan;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\UjianPesertaKategori;
use App\Models\User;

function makeUjianPeserta(array $attributes = []): UjianPeserta
{
    $peserta = new UjianPeserta;
    $peserta->forceFill(array_merge([
        'ujian_id' => Ujian::factory()->create()->id,
        'user_id' => User::factory()->create()->id,
        'status' => 'sedang_ujian',
    ], $attributes))->save();

    return $peserta;
}

describe('UjianPeserta kategori relation', function () {
    it('has many kategori rows', function () {
        $peserta = makeUjianPeserta();

        UjianPesertaKategori::factory()->count(2)->create([
            'ujian_peserta_id' => $peserta->id,
        ]);

        expect($peserta->kategori()->count())->toBe(2);
    });

    it('exposes the inverse ujianPeserta relation', function () {
        $peserta = makeUjianPeserta();

        $kategori = UjianPesertaKategori::factory()->create([
            'ujian_peserta_id' => $peserta->id,
        ]);

        expect($kategori->ujianPeserta->id)->toBe($peserta->id);
    });

    it('relates a kategori to its jenis ujian', function () {
        $peserta = makeUjianPeserta();
        $jenis = JenisUjian::factory()->create();

        $kategori = UjianPesertaKategori::factory()->create([
            'ujian_peserta_id' => $peserta->id,
            'jenis_ujian_id' => $jenis->id,
        ]);

        expect($kategori->jenisUjian->id)->toBe($jenis->id);
    });
});

describe('UjianPeserta langganan relation', function () {
    it('belongs to a langganan when set', function () {
        $langganan = PesertaLangganan::create([
            'user_id' => User::factory()->create()->id,
            'paket_id' => Paket::factory()->create()->id,
            'status' => 'active',
        ]);

        $peserta = makeUjianPeserta(['langganan_id' => $langganan->id]);

        expect($peserta->langganan->id)->toBe($langganan->id);
    });

    it('has a null langganan when not set', function () {
        $peserta = makeUjianPeserta();

        expect($peserta->langganan)->toBeNull();
    });
});
