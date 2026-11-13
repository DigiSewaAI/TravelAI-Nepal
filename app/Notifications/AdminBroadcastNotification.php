<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AdminBroadcastNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $subjectText,
        public string $messageText
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subjectText)
            ->greeting('Hello ' . ($notifiable->name ?? 'User'))
            ->line($this->messageText)
            ->action('Visit TravelAI Nepal', url('/'))
            ->line('Thank you for using TravelAI Nepal!');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->subjectText,
            'message' => $this->messageText,
            'type' => 'admin_broadcast',
            'category' => 'announcement',
            'url' => url('/'),
        ];
    }
}