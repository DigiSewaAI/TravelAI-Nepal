<?php
namespace App\Mail;
use App\Models\WholesaleRfq;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
class WholesaleRfqMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public WholesaleRfq $rfq, public string $type, public string $role = 'provider', public ?string $messageText = null) {}
    public function envelope(): Envelope
    {
        $subjects = ['submitted' => 'New RFQ Received: ', 'confirmation' => 'RFQ Submitted: ', 'quoted' => 'Quote Received: ', 'accepted' => 'Quote Accepted: ', 'rejected' => 'Quote Rejected: ', 'message' => 'New Message: '];
        return new Envelope(subject: ($subjects[$this->type] ?? 'RFQ Update: ').$this->rfq->rfq_number);
    }
    public function content(): Content { return new Content(view: 'emails.wholesale-rfq'); }
}