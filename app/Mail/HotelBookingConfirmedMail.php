<?php

namespace App\Mail;

use App\Models\HotelBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HotelBookingConfirmedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public HotelBooking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmed ✅ — ' . ($this->booking->provider->name ?? 'Hotel'),
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing(['room', 'provider']);

        $logoData = null;
        $provider = $this->booking->provider;
        if ($provider && $provider->logo_url) {
            $logoPath = storage_path('app/public/' . $provider->logo_url);
            if (file_exists($logoPath)) {
                $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                $mime = ($ext === 'jpg') ? 'jpeg' : $ext;
                $logoData = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        return new Content(
            view: 'emails.hotel-booking-confirmed',
            with: [
                'booking' => $this->booking,
                'provider' => $provider,
                'room' => $this->booking->room,
                'logoData' => $logoData,
            ]
        );
    }
}