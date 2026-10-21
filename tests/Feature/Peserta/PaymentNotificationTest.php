<?php

use App\Mail\PaymentRejectedMail;
use App\Mail\PaymentVerifiedMail;
use App\Models\Paket;
use App\Models\PaymentNotification;
use App\Models\PesertaPembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

describe('Payment Notification System', function () {

    beforeEach(function () {
        Mail::fake();
        $this->admin = User::factory()->create([
            'is_peserta' => false,
            'is_superadmin' => true,
            'email' => 'admin@panritta.com',
        ]);
        $this->peserta = User::factory()->create([
            'is_peserta' => true,
            'email' => 'peserta@test.com',
        ]);
        $this->paket = Paket::factory()->create([
            'harga' => 99000,
            'durasi_hari' => 30,
            'kuota_ujian' => 5,
        ]);
        $this->pembayaran = PesertaPembayaran::factory()->create([
            'user_id' => $this->peserta->id,
            'paket_id' => $this->paket->id,
            'status' => 'pending',
            'bukti_pembayaran' => 'pembayaran/test.jpg',
        ]);
    });

    it('creates in-app notification when payment is verified', function () {
        $this->actingAs($this->admin);

        $this->post(route('superadmin.pembayaran.verifikasi', $this->pembayaran), [
            'catatan_verifikasi' => 'Pembayaran sesuai',
        ]);

        $this->assertDatabaseHas('payment_notifications', [
            'user_id' => $this->peserta->id,
            'pembayaran_id' => $this->pembayaran->id,
            'type' => 'verified',
        ]);
    });

    it('sends email when payment is verified', function () {
        $this->actingAs($this->admin);

        $this->post(route('superadmin.pembayaran.verifikasi', $this->pembayaran), [
            'catatan_verifikasi' => 'Pembayaran sesuai',
        ]);

        Mail::assertSent(PaymentVerifiedMail::class, function ($mail) {
            return $mail->hasTo($this->peserta->email);
        });
    });

    it('creates in-app notification when payment is rejected', function () {
        $this->actingAs($this->admin);

        $this->post(route('superadmin.pembayaran.tolak', $this->pembayaran), [
            'alasan' => 'Nominal tidak sesuai',
        ]);

        $this->assertDatabaseHas('payment_notifications', [
            'user_id' => $this->peserta->id,
            'pembayaran_id' => $this->pembayaran->id,
            'type' => 'rejected',
        ]);
    });

    it('sends email when payment is rejected', function () {
        $this->actingAs($this->admin);

        $this->post(route('superadmin.pembayaran.tolak', $this->pembayaran), [
            'alasan' => 'Nominal tidak sesuai',
        ]);

        Mail::assertSent(PaymentRejectedMail::class, function ($mail) {
            return $mail->hasTo($this->peserta->email);
        });
    });

    it('peserta can view notification list', function () {
        $this->actingAs($this->peserta);

        PaymentNotification::create([
            'user_id' => $this->peserta->id,
            'pembayaran_id' => $this->pembayaran->id,
            'type' => 'verified',
            'title' => 'Test Notification',
            'message' => 'This is a test',
        ]);

        $response = $this->get(route('peserta.notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Notification');
    });

    it('marks notification as read when viewed', function () {
        $notification = PaymentNotification::create([
            'user_id' => $this->peserta->id,
            'pembayaran_id' => $this->pembayaran->id,
            'type' => 'verified',
            'title' => 'Test',
            'message' => 'Test',
        ]);

        expect($notification->isUnread())->toBe(true);

        $this->actingAs($this->peserta)
            ->get(route('peserta.notifications.show', $notification));

        $notification->refresh();
        expect($notification->isUnread())->toBe(false);
    });

    it('can mark all notifications as read', function () {
        PaymentNotification::create([
            'user_id' => $this->peserta->id,
            'pembayaran_id' => $this->pembayaran->id,
            'type' => 'verified',
            'title' => 'Test 1',
            'message' => 'Test',
        ]);
        PaymentNotification::create([
            'user_id' => $this->peserta->id,
            'pembayaran_id' => $this->pembayaran->id,
            'type' => 'rejected',
            'title' => 'Test 2',
            'message' => 'Test',
        ]);

        $this->actingAs($this->peserta)
            ->post(route('peserta.notifications.mark-all-read'));

        $unreadCount = PaymentNotification::where('user_id', $this->peserta->id)
            ->whereNull('read_at')
            ->count();

        expect($unreadCount)->toBe(0);
    });

    it('prevents viewing other users notifications', function () {
        $otherUser = User::factory()->create(['is_peserta' => true]);
        $notification = PaymentNotification::create([
            'user_id' => $otherUser->id,
            'pembayaran_id' => $this->pembayaran->id,
            'type' => 'verified',
            'title' => 'Private',
            'message' => 'Private',
        ]);

        $response = $this->actingAs($this->peserta)
            ->get(route('peserta.notifications.show', $notification));

        $response->assertStatus(404);
    });
});
