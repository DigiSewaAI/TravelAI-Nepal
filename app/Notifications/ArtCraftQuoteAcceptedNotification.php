<?php

namespace App\Notifications;

use App\Models\CustomOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ArtCraftQuoteAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(public CustomOrder $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'artcraft_quote_accepted',
            'title'   => 'Quote Accepted',
            'message' => $this->order->client_name . ' accepted your quote for order ' . $this->order->booking_number,
            'url'     => url('/provider/art-craft/custom-orders/' . $this->order->id),
            'order_id' => $this->order->id,
        ];
    }
}