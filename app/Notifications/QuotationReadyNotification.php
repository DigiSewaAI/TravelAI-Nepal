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
    
    // If no text, generate from final or data
    if (empty($quotationText)) {
        if ($quotationRequest->quotation_final) {
            $controller = app(\App\Http\Controllers\Provider\QuotationRequestController::class);
            $quotationText = $controller->formatQuotationText(
                $quotationRequest->quotation_final,
                $quotationRequest->provider,
                $quotationRequest
            );
        } else {
            $quotationText = $quotationRequest->quotation_text ?? 'Quotation not available.';
        }
    }

    return (new MailMessage)
        ->subject('📄 Your Quotation is Ready!')
        ->greeting('Hello ' . ($quotationRequest->traveler_name ?? 'Traveler') . '!')
        ->line("Your quotation from **{$quotationRequest->provider->name}** is ready.")
        ->line("Please find the quotation details below:")
        ->line('')
        ->line($quotationText)
        ->action('View Quotation', route('traveler.dashboard'))
        ->line('Thank you for using TravelAI Nepal!');
}
}