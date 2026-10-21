<?php

namespace App\Mail;

use App\Models\PesertaPembayaran;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PesertaPembayaran $pembayaran,
        public string $alasan = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pembayaran Perlu Diverifikasi Ulang - Panritta',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-rejected',
            with: [
                'peserta' => $this->pembayaran->user,
                'paket' => $this->pembayaran->paket,
                'pembayaran' => $this->pembayaran,
                'alasan' => $this->alasan,
            ],
        );
    }
}
