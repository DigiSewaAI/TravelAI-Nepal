<?php

namespace App\Notifications;

use App\Models\TransportBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public TransportBooking $booking,
        public string $reason
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'transport_payment_rejected',
            'title' => 'Payment Proof Rejected',
            'message' => 'Your payment proof for ' . ($this->booking->booking_ref ?? '') . ' was rejected. Reason: ' . $this->reason,
            'url' => url('/traveler/transport/bookings/' . $this->booking->id),
            'booking_id' => $this->booking->id,
            'reason' => $this->reason,
        ];
    }
}