<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public Subscription $subscription;
    public int $daysRemaining;

    public function __construct(Subscription $subscription, int $daysRemaining)
    {
        $this->subscription = $subscription;
        $this->daysRemaining = $daysRemaining;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your subscription expires in ' . $this->daysRemaining . ' days',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-expiring',
            with: [
                'subscription' => $this->subscription,
                'provider' => $this->subscription->provider,
                'plan' => $this->subscription->plan,
                'daysRemaining' => $this->daysRemaining,
            ]
        );
    }
}