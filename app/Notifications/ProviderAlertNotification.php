<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProviderAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $category,
        public ?string $url = null
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        if (empty($notifiable->email)) {
            return $channels;
        }
        if (in_array($this->category, ['order', 'booking', 'wholesale_rfq', 'rfq', 'payment'], true)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $mail = (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('[TravelAI] ' . $this->title)
            ->greeting('Hello ' . ($notifiable->name ?? 'Provider'))
            ->line($this->message);
        if (!empty($this->url)) {
            $mail->action('View Details', $this->url);
        }
        return $mail->line('Thank you for using TravelAI Nepal!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'provider_alert',
            'category' => $this->category,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url ?? route('provider.dashboard'),
        ];
    }
}