<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice #' . $this->invoice->invoice_number . ' from TravelAI Nepal',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice'  => $this->invoice,
                'provider' => $this->invoice->provider,
            ]
        );
    }

    public function attachments(): array
    {
        // Generate PDF at queue-process time (serialization-safe)
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice'      => $this->invoice,
            'provider'     => $this->invoice->provider,
            'subscription' => $this->invoice->subscription,
            'booking'      => $this->invoice->booking,
        ]);

        return [
            Attachment::fromData(fn() => $pdf->output(), 'invoice-' . $this->invoice->invoice_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}