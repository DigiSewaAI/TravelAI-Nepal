<?php

namespace App\Mail;

use App\Models\TransportBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransportBookingCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public TransportBooking $booking
    ) {}

    public function envelope(): Envelope
    {
        $providerName = $this->booking->provider->name ?? 'Transport Operator';
        return new Envelope(
            subject: 'Booking Request Received — ' . $providerName,
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing(['provider', 'vehicle']);

        return new Content(
            view: 'emails.transport-booking-created',
            with: [
                'booking' => $this->booking,
                'provider' => $this->booking->provider,
                'vehicle' => $this->booking->vehicle,
            ]
        );
    }
}