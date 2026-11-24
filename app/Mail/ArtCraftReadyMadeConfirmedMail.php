<?php

namespace App\Mail;

use App\Models\CustomOrder;
use App\Models\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ArtCraftReadyMadeConfirmedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public CustomOrder $order, public Provider $provider) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✓ Your Artwork Reservation Confirmed — ' . $this->order->booking_number,
        );
    }

    public function content(): Content
    {
        $statusUrl = URL::temporarySignedRoute(
            'public.artcraft.order.status',
            now()->addDays(90),
            ['order' => $this->order->id]
        );

        return new Content(
            view: 'emails.artcraft-ready-made-confirmed',
            with: [
                'order' => $this->order,
                'provider' => $this->provider,
                'statusUrl' => $statusUrl,
            ]
        );
    }
}