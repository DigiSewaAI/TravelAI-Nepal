<?php

namespace App\Notifications;

use App\Models\QuotationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationReadyNotification extends Notification
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
    $quotationRequest = $this->quotationRequest;
    $quotationText = $quotationRequest->quotation_text;

    return (new MailMessage)
        ->subject('📄 Your Quotation is Ready!')
        ->greeting('Hello ' . ($quotationRequest->traveler_name ?? $quotationRequest->traveler->name ?? 'Traveler') . '!')
        ->line("Your quotation from **{$quotationRequest->provider->name}** is ready.")
        ->line("Please find the quotation details below:")
        ->line('')
        ->line($quotationText)
        ->action('View Quotation', route('traveler.dashboard'))
        ->line('Thank you for using TravelAI Nepal!');
}

    public function toArray($notifiable)
    {
        return [
            'quotation_request_id' => $this->quotationRequest->id,
            'provider_name' => $this->quotationRequest->provider->name,
            'message' => 'Your quotation is ready.',
        ];
    }
}