<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentNoticeReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Traveler marked payment as sent — Booking #' . $this->booking->id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-notice-received',
            with: [
                'booking' => $this->booking,
                'service' => $this->booking->service,
                'provider' => $this->booking->service->provider ?? null,
                'traveler' => $this->booking->traveler,
            ]
        );
    }
}