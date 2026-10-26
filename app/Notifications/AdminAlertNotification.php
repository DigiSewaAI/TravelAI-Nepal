<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $category,
        public ?string $url = null,
        public ?int $orderId = null
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'admin_alert',
            'category' => $this->category,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url ?? route('admin.dashboard'),
            'order_id' => $this->orderId,
        ];
    }
}