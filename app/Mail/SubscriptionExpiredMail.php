<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public Subscription $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your subscription has expired',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-expired',
            with: [
                'subscription' => $this->subscription,
                'provider' => $this->subscription->provider,
                'plan' => $this->subscription->plan,
            ]
        );
    }
}