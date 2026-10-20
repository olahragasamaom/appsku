<?php

use App\Models\Paket;
use App\Models\PesertaPembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('Peserta Payment Flow', function () {

    beforeEach(function () {
        Storage::fake('public');
        $this->peserta = User::factory()->create(['is_peserta' => true]);
        $this->paket = Paket::factory()->create(['harga' => 99000]);
    });

    it('creates payment when peserta chooses a paid package', function () {
        $this->actingAs($this->peserta);

        $response = $this->post(route('peserta.langganan.pilih', $this->paket));

        $response->assertRedirect();
        $this->assertDatabaseHas('panritta_peserta_pembayaran', [
            'user_id' => $this->peserta->id,
            'paket_id' => $this->paket->id,
            'status' => 'pending',
        ]);
    });

    it('can view payment details', function () {
        $this->actingAs($this->peserta);

        $pembayaran = PesertaPembayaran::factory()->create([
            'user_id' => $this->peserta->id,
            'paket_id' => $this->paket->id,
            'status' => 'pending',
        ]);

        $response = $this->get(route('peserta.langganan.bayar', $pembayaran));

        $response->assertStatus(200);
        $response->assertSee($pembayaran->nomor_pembayaran);
        $response->assertSee('Rp ' . number_format($pembayaran->jumlah, 0, ',', '.'));
    });

    it('can upload bukti pembayaran', function () {
        $this->actingAs($this->peserta);

        $pembayaran = PesertaPembayaran::factory()->create([
            'user_id' => $this->peserta->id,
            'paket_id' => $this->paket->id,
            'status' => 'pending',
        ]);

        $file = UploadedFile::fake()->image('bukti.jpg');
        $catatan = 'Transfer dari BCA atas nama Budi';

        $response = $this->post(route('peserta.langganan.upload-bukti', $pembayaran), [
            'bukti_pembayaran' => $file,
            'catatan_peserta' => $catatan,
        ]);

        $response->assertRedirect(route('peserta.langganan.status', $pembayaran));
        $pembayaran->refresh();
        expect($pembayaran->bukti_pembayaran)->not->toBeNull();
        expect($pembayaran->catatan_peserta)->toBe($catatan);
        expect($pembayaran->status)->toBe('pending');
    });

    it('validates bukti pembayaran file', function () {
        $this->actingAs($this->peserta);

        $pembayaran = PesertaPembayaran::factory()->create([
            'user_id' => $this->peserta->id,
            'status' => 'pending',
        ]);

        $response = $this->post(route('peserta.langganan.upload-bukti', $pembayaran), [
            'bukti_pembayaran' => 'not-a-file',
        ]);

        $response->assertSessionHasErrors('bukti_pembayaran');
    });

    it('can view payment status', function () {
        $this->actingAs($this->peserta);

        $pembayaran = PesertaPembayaran::factory()->create([
            'user_id' => $this->peserta->id,
            'status' => 'pending',
        ]);

        $response = $this->get(route('peserta.langganan.status', $pembayaran));

        $response->assertStatus(200);
        $response->assertSee($pembayaran->nomor_pembayaran);
        $response->assertSee('Menunggu Verifikasi');
    });

});
