<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Route;

class BookingWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public bool $isRegistered,
        public array $brand = []
    ) {}

    public function envelope(): Envelope
    {
        $number = $this->booking->booking_number ?? '#' . $this->booking->id;

        return new Envelope(
            subject: __('messages.email_booking_welcome_subject', ['number' => $number]),
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing(['service.provider', 'traveler']);

        $registerUrl = Route::has('register') ? route('register') : url('/register');
        $loginUrl    = Route::has('login')    ? route('login')    : url('/login');

        return new Content(
            view: 'emails.booking-welcome',
            with: [
                'booking'      => $this->booking,
                'traveler'     => $this->booking->traveler,
                'service'      => $this->booking->service,
                'provider'     => $this->booking->service?->provider,
                'isRegistered' => $this->isRegistered,
                'registerUrl'  => $registerUrl,
                'loginUrl'     => $loginUrl,
                'brand'        => $this->brand,
            ]
        );
    }
}