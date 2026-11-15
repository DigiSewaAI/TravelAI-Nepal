<?php

namespace App\Notifications;

use App\Models\TransportBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentProofSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public TransportBooking $booking) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        try {
            $url = route('provider.transport.bookings.show', $this->booking->id);
        } catch (\Throwable $e) {
            $url = url('/provider/transport/bookings/' . $this->booking->id);
        }

        return [
            'type' => 'transport_payment_proof',
            'title' => 'Payment Proof Submitted',
            'message' => ($this->booking->passenger_name ?? 'Customer')
                . ' submitted payment proof for ' . ($this->booking->booking_ref ?? '')
                . ' — Rs ' . number_format((float) ($this->booking->total_amount ?? $this->booking->amount ?? 0), 2),
            'url' => $url,
            'booking_id' => $this->booking->id,
        ];
    }
}