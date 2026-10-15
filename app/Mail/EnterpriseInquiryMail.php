<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnterpriseInquiryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Enterprise Inquiry: ' . ($this->data['company_name'] ?? 'New Lead'),
            replyTo: [new Address($this->data['email'], $this->data['contact_person'])],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enterprise-inquiry',
            with: ['data' => $this->data]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}