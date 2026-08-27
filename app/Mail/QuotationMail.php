<?php

namespace App\Mail;

use App\Models\QuotationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public QuotationRequest $quotationRequest;

    public function __construct(QuotationRequest $quotationRequest)
    {
        $this->quotationRequest = $quotationRequest;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Quotation from ' . $this->quotationRequest->provider->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quotation',
            with: [
                'quotationRequest' => $this->quotationRequest,
                'quotationText' => $this->quotationRequest->quotation_text,
                'provider' => $this->quotationRequest->provider,
                'travelerName' => $this->quotationRequest->traveler_name ?? 'Traveler',
            ]
        );
    }
}