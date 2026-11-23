<?php

namespace App\Notifications;

use App\Models\CustomOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ArtCraftRevisionRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CustomOrder $order,
        public string $customerResponse
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'artcraft_revision_requested',
            'title'   => 'Revision Requested',
            'message' => $this->order->client_name . ' requested changes on ' . $this->order->booking_number,
            'response' => \Illuminate\Support\Str::limit($this->customerResponse, 150),
            'url'     => url('/provider/art-craft/custom-orders/' . $this->order->id),
            'order_id' => $this->order->id,
        ];
    }
}