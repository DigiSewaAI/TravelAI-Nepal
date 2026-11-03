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
        $channels = ['database'];
        if (empty($notifiable->email)) {
            return $channels;
        }
        if (in_array($this->category, ['sos', 'payment', 'verification'], true)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $mail = (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('[Admin] ' . $this->title)
            ->greeting('Hello ' . ($notifiable->name ?? 'Admin'))
            ->line($this->message);
        if (!empty($this->url)) {
            $mail->action('View in Admin', $this->url);
        }
        return $mail->line('TravelAI Nepal — Admin Alert');
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