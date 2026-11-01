<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
class UserAlertNotification extends Notification
{
    use Queueable;
    public function __construct(
        public string $title,
        public string $message,
        public string $category = 'general',
        public ?string $url = null
    ) {}
    public function via($notifiable): array { return ['database']; }
    public function toDatabase($notifiable): array {
        return ['type' => 'user_alert', 'category' => $this->category, 'title' => $this->title, 'message' => $this->message, 'url' => $this->url];
    }
}