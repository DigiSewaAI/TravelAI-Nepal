<?php

namespace App\Notifications;

use App\Models\CustomOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewArtCraftEnquiryNotification extends Notification
{
    use Queueable;

    public function __construct(public CustomOrder $order) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        try {
            $url = route('art-craft.orders.show', $this->order->id);
        } catch (\Throwable $e) {
            $url = url('/art-craft/custom-orders/' . $this->order->id);
        }

        $kind = $this->order->request_type === 'ready_made' ? 'Ready-made enquiry' : 'Custom commission request';
        $detail = $this->order->request_type === 'ready_made'
            ? ($this->order->artwork_name_snapshot ?? 'artwork')
            : ($this->order->type ?? 'custom');

        return [
            'type' => 'artcraft_enquiry',
            'title' => 'New ArtCraft ' . ($this->order->request_type === 'ready_made' ? 'Enquiry' : 'Commission'),
            'message' => ($this->order->client_name ?? 'Customer') . ' — ' . $kind . ' — ' . $detail,
            'url' => $url,
            'order_id' => $this->order->id,
            'booking_number' => $this->order->booking_number,
        ];
    }
}