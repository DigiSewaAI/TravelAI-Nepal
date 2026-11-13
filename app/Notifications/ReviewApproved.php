<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReviewApproved extends Notification
{
    use Queueable;

    public function __construct(public Review $review) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        $service = $this->review->service?->name ?? 'the service';
        return (new MailMessage)
            ->subject('Your review has been approved')
            ->greeting('Namaste ' . ($notifiable->name ?? 'Traveler'))
            ->line("Your review for {$service} has been approved and is now public.")
            ->action('View Review', route('public.services.show', $this->review->service->slug ?? '#'))
            ->line('Thank you for your feedback!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type'         => 'review_approved',
            'category'     => 'review',
            'title'        => 'Review Approved',
            'message'      => 'Your review for ' . ($this->review->service?->name ?? 'service') . ' is now live.',
            'review_id'    => $this->review->id,
            'url'          => route('traveler.reviews.index'),
        ];
    }
}