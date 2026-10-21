<?php

namespace App\Mail;

use App\Models\PesertaPembayaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PesertaPembayaran $pembayaran) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembayaran Paket Terverifikasi - Panritta',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-verified',
            with: [
                'peserta' => $this->pembayaran->user,
                'paket' => $this->pembayaran->paket,
                'pembayaran' => $this->pembayaran,
            ],
        );
    }
}
