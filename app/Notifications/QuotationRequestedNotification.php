<?php

namespace App\Notifications;

use App\Models\QuotationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationRequestedNotification extends Notification
{
    use Queueable;

    protected QuotationRequest $quotationRequest;

    public function __construct(QuotationRequest $quotationRequest)
    {
        $this->quotationRequest = $quotationRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $input = $this->quotationRequest->traveler_input;
        $traveler = $this->quotationRequest->traveler;

        return (new MailMessage)
            ->subject('📩 New Quotation Request Received')
            ->greeting('Hello ' . $this->quotationRequest->provider->name . '!')
            ->line("A traveler has requested a quotation based on their itinerary.")
            ->line("**Traveler:** " . ($traveler ? $traveler->name : 'Guest'))
            ->line("**Destination:** " . ($input['destination'] ?? 'N/A'))
            ->line("**Days:** " . ($input['days'] ?? 'N/A'))
            ->line("**Budget:** $" . ($input['budget'] ?? 'N/A'))
            ->action('View Request', route('provider.quotation-requests.show', $this->quotationRequest))
            ->line('Please review the itinerary and generate a quotation.');
    }

    public function toArray($notifiable)
    {
        return [
            'quotation_request_id' => $this->quotationRequest->id,
            'traveler_name' => $this->quotationRequest->traveler->name ?? 'Guest',
            'destination' => $this->quotationRequest->traveler_input['destination'] ?? 'N/A',
            'message' => 'New quotation request received.',
        ];
    }
}