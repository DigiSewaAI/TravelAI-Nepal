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
        return ['database'];
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