<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReviewRejected extends Notification
{
    use Queueable;

    public function __construct(
        public Review $review,
        public ?string $reason = null
    ) {}

    public function via($notifiable): array { return ['mail', 'database']; }

    public function toMail($notifiable): MailMessage
    {
        $service = $this->review->service?->name ?? 'service';
        $mail = (new MailMessage)
            ->subject('Your review was not approved')
            ->greeting('Namaste ' . ($notifiable->name ?? 'Traveler'))
            ->line("Your review for {$service} was not approved.");
        if ($this->reason) $mail->line("Reason: {$this->reason}");
        return $mail->line('If you believe this is a mistake, please contact support.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type'      => 'review_rejected',
            'category'  => 'review',
            'title'     => 'Review Not Approved',
            'message'   => $this->reason ?: 'Your review was not approved.',
            'review_id' => $this->review->id,
            'url'       => route('traveler.reviews.index'),
        ];
    }
}