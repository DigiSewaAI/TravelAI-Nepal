<?php

namespace App\Mail;

use App\Models\SosAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SosAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SosAlert $sos) {}

    public function envelope(): Envelope
    {
        $travelerName = $this->sos->traveler->name ?? 'Traveler';
        return new Envelope(
            subject: '🚨 SOS ALERT — ' . $travelerName . ' needs help',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sos_alert',
        );
    }
}