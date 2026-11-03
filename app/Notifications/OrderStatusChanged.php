<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        if (empty($notifiable->email)) {
            return $channels;
        }
        if (in_array($this->newStatus, ['cancelled', 'delivered'], true)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('Order ' . $this->order->order_number . ' - ' . ucfirst($this->newStatus))
            ->greeting('Hello ' . ($notifiable->name ?? 'Customer'))
            ->line('Your order ' . $this->order->order_number . ' status: ' . $this->oldStatus . ' → ' . $this->newStatus)
            ->action('View Order', route('orders.show', $this->order->id))
            ->line('Thank you for using TravelAI Nepal!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'order_status_changed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => "Your order {$this->order->order_number} is now {$this->newStatus}",
            'url' => route('orders.show', $this->order->id),
        ];
    }
}