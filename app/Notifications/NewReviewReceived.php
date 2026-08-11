<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewReceived extends Notification
{
    use Queueable;

    protected $review;

    public function __construct(Review $review)
    {
        $this->review = $review;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Review Received')
            ->line("A new review has been posted for service: {$this->review->service->name}")
            ->line("Rating: {$this->review->rating} ⭐")
            ->line("Comment: {$this->review->comment}")
            ->action('View Review', route('admin.reviews.show', $this->review))
            ->line('Thank you for using TravelAI Nepal!');
    }

    public function toArray($notifiable)
    {
        return [
            'review_id' => $this->review->id,
            'service_name' => $this->review->service->name,
            'rating' => $this->review->rating,
            'message' => "New review received for {$this->review->service->name}",
        ];
    }
}