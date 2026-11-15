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

        $logoData = null;
        $logoUrl = null;
        $provider = $this->booking->provider;

        if ($provider && $provider->logo_url) {
            $logoPath = storage_path('app/public/' . $provider->logo_url);
            if (file_exists($logoPath)) {
                $size = filesize($logoPath);
                // Logo <= 50KB: inline base64 (email self-contained)
                // Logo > 50KB: public URL (avoids Gmail 102KB clipping)
                if ($size <= 51200) {
                    $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
                    $mime = ($ext === 'jpg') ? 'jpeg' : $ext;
                    $logoData = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
                } else {
                    $logoUrl = url('storage/' . $provider->logo_url);
                }
            }
        }

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