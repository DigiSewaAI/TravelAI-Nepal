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
        // ✅ Ensure we use the latest quotation text
        // If final exists and status is sent or edited, use it
        $quotationText = $this->quotationRequest->quotation_text;
        
        // If no quotation text, generate one from final or data
        if (empty($quotationText)) {
            $provider = $this->quotationRequest->provider;
            $controller = app(\App\Http\Controllers\Provider\QuotationRequestController::class);
            
            if ($this->quotationRequest->quotation_final) {
                $quotationText = $controller->formatQuotationText(
                    $this->quotationRequest->quotation_final,
                    $provider,
                    $this->quotationRequest
                );
            } elseif ($this->quotationRequest->quotation_data) {
                $quotationText = $controller->formatQuotationText(
                    $this->quotationRequest->quotation_data,
                    $provider,
                    $this->quotationRequest
                );
            }
        }

        return new Content(
            view: 'emails.quotation',
            with: [
                'quotationRequest' => $this->quotationRequest,
                'quotationText' => $quotationText,
                'provider' => $this->quotationRequest->provider,
                'travelerName' => $this->quotationRequest->traveler_name ?? 'Traveler',
            ]
        );
    }
}