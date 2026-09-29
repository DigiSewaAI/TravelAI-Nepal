<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Payment $payment;
    public Subscription $subscription;
    public ?Invoice $invoice;
    public $pdf;

    public function __construct(
        Payment $payment,
        Subscription $subscription,
        ?Invoice $invoice = null,
        $pdf = null
    ) {
        $this->payment = $payment;
        $this->subscription = $subscription;
        $this->invoice = $invoice;
        $this->pdf = $pdf;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Verified — ' . ($this->subscription->plan->name ?? 'Your Subscription') . ' Activated',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-verified',
            with: [
                'payment' => $this->payment,
                'subscription' => $this->subscription,
                'provider' => $this->subscription->provider,
                'plan' => $this->subscription->plan,
                'invoice' => $this->invoice,
            ]
        );
    }

    public function attachments(): array
    {
        if (!$this->pdf || !$this->invoice) {
            return [];
        }

        return [
            Attachment::fromData(fn() => $this->pdf->output(), 'invoice-' . $this->invoice->invoice_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}