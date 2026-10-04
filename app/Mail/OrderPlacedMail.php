<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Provider;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public Provider $provider
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Order Received — ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.orders.placed');
    }
}