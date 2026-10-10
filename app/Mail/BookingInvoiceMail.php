<?php

namespace App\Mail;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingInvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public array $brand
    ) {}

    public function envelope(): Envelope
    {
        $number = $this->booking->booking_number ?? '#' . $this->booking->id;
        $providerEmail = $this->booking->service->provider->contact_email ?? null;

        return new Envelope(
            subject: 'Booking Invoice ' . $number . ' from ' . ($this->brand['name'] ?? 'TravelAI Nepal'),
            replyTo: $providerEmail
                ? [new Address($providerEmail, $this->brand['name'] ?? 'Provider')]
                : [],
        );
    }

    public function content(): Content
    {
        $provider = $this->booking->service->provider;

        return new Content(
            view: 'emails.booking-invoice',
            with: [
                'booking'  => $this->booking,
                'traveler' => $this->booking->traveler,
                'provider' => $provider,
                'brand'    => $this->brand,
                '__provider_reply_to' => [
                    'address' => $provider->contact_email ?? null,
                    'name'    => $this->brand['name'] ?? $provider->name,
                ],
            ]
        );
    }

    public function attachments(): array
    {
        $provider = $this->booking->service->provider;

        $pdf = Pdf::loadView('invoices.booking-pdf', [
            'booking'  => $this->booking,
            'service'  => $this->booking->service,
            'provider' => $provider,
            'traveler' => $this->booking->traveler,
            'brand'    => $this->brand,
        ]);

        $filename = 'booking-invoice-' . ($this->booking->booking_number ?? $this->booking->id) . '.pdf';

        $attachments = [
            Attachment::fromData(fn() => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];

        if (!empty($this->brand['logo_base64'])) {
            $logoBytes = base64_decode($this->brand['logo_base64']);
            $attachments[] = Attachment::fromData(fn() => $logoBytes, 'provider-logo.png')
                ->withMime($this->brand['logo_mime'] ?? 'image/png')
                ->as('provider-logo');
        }

        return $attachments;
    }
}