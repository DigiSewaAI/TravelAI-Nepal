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
    public function via($notifiable): array
    {
        $channels = ['database'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $mail = (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject($this->title)
            ->greeting('Hello ' . ($notifiable->name ?? 'Traveler'))
            ->line($this->message);
        if (!empty($this->url)) {
            $mail->action('View Details', $this->url);
        }
        return $mail->line('Thank you for using TravelAI Nepal!');
    }
    public function toDatabase($notifiable): array {
        return ['type' => 'user_alert', 'category' => $this->category, 'title' => $this->title, 'message' => $this->message, 'url' => $this->url];
    }
}