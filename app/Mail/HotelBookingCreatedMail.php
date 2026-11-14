<?php

namespace App\Mail;

use App\Models\HotelBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HotelBookingCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public HotelBooking $booking
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmed — ' . ($this->booking->provider->name ?? 'Hotel'),
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing(['room', 'provider']);

        return new Content(
            view: 'emails.hotel-booking-created',
            with: [
                'booking' => $this->booking,
                'provider' => $this->booking->provider,
                'room' => $this->booking->room,
            ]
        );
    }
}