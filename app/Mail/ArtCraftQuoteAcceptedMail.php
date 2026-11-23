<?php

namespace App\Mail;

use App\Models\CustomOrder;
use App\Models\CustomOrderQuote;
use App\Models\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ArtCraftQuoteAcceptedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CustomOrder $order,
        public CustomOrderQuote $quote,
        public Provider $provider
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✓ Quote Accepted — ' . $this->order->booking_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.artcraft-quote-accepted',
            with: [
                'order'    => $this->order,
                'quote'    => $this->quote,
                'provider' => $this->provider,
            ]
        );
    }
}