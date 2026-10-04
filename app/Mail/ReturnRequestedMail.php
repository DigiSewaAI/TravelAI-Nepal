<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public OrderItem $item) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Return Requested — ' . $this->order->order_number);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.orders.return-requested');
    }
}