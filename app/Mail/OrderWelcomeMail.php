<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Route;

class OrderWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public bool $isRegistered,
        public array $brand = []
    ) {}

    public function envelope(): Envelope
    {
        $number = $this->order->order_number ?? '#' . $this->order->id;

        return new Envelope(
            subject: __('messages.email_order_welcome_subject', ['number' => $number]),
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing(['user', 'items']);

        $registerUrl = Route::has('register') ? route('register') : url('/register');
        $loginUrl    = Route::has('login')    ? route('login')    : url('/login');

        return new Content(
            view: 'emails.order-welcome',
            with: [
                'order'        => $this->order,
                'user'         => $this->order->user,
                'itemsCount'   => $this->order->items->count(),
                'isRegistered' => $this->isRegistered,
                'registerUrl'  => $registerUrl,
                'loginUrl'     => $loginUrl,
                'brand'        => $this->brand,
            ]
        );
    }
}