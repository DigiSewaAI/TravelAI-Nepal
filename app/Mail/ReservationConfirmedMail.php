<?php

namespace App\Mail;

use App\Models\RestaurantReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public RestaurantReservation $reservation
    ) {}

    public function envelope(): Envelope
    {
        $providerName = $this->reservation->provider->name ?? 'Restaurant';
        return new Envelope(
            subject: 'Reservation Confirmed ✅ — ' . $providerName,
        );
    }

    public function content(): Content
    {
        $this->reservation->loadMissing(['provider', 'table']);
        
        return new Content(
            view: 'emails.restaurant-reservation-confirmed',
            with: [
                'reservation' => $this->reservation,
                'provider' => $this->reservation->provider,
            ]
        );
    }
}