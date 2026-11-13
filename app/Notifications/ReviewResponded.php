<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;

class ReviewResponded extends Notification
{
    use Queueable;

    public function __construct(public Review $review) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        $service = $this->review->service?->name ?? 'the service';
        return (new MailMessage)
            ->subject('Provider responded to your review')
            ->greeting('Namaste ' . ($notifiable->name ?? 'Traveler'))
            ->line("The provider for {$service} has responded to your review.")
            ->line('"' . Str::limit($this->review->provider_response, 150) . '"')
            ->action('View Response', route('traveler.reviews.index'))
            ->line('Thank you for choosing TravelAI Nepal.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type'      => 'review_responded',
            'category'  => 'review',
            'title'     => 'Provider Responded',
            'message'   => 'Provider responded to your review for ' . ($this->review->service?->name ?? 'service') . '.',
            'review_id' => $this->review->id,
            'url'       => route('traveler.reviews.index'),
        ];
    }
}