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

                $provider = $this->booking->provider;
        $logo = $provider
            ? app(\App\Services\TransportReceiptService::class)->buildLogoForEmail($provider)
            : ['logoData' => null, 'logoUrl' => null];
        $logoData = $logo['logoData'];
        $logoUrl  = $logo['logoUrl'];

                // Registered user check + CTA URLs (BookingWelcomeMail pattern)
        $isRegistered = \App\Models\User::where('email', $this->booking->passenger_email)->exists();
        $registerUrl = \Illuminate\Support\Facades\Route::has('register') ? route('register') : url('/register');
        $loginUrl = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/login');
        $dashboardUrl = \Illuminate\Support\Facades\Route::has('traveler.dashboard-v2')
            ? route('traveler.dashboard-v2')
            : url('/traveler/dashboard-v2');

        return new Content(
            view: 'emails.transport-booking-created',
            with: [
                'booking' => $this->booking,
                'provider' => $provider,
                'vehicle' => $this->booking->vehicle,
                'logoData' => $logoData,
                'logoUrl' => $logoUrl,
                'isRegistered' => $isRegistered,
                'registerUrl' => $registerUrl,
                'loginUrl' => $loginUrl,
                'dashboardUrl' => $dashboardUrl,
            ]
        );
    }
}