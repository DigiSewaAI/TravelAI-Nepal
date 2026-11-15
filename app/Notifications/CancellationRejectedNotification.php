<?php
namespace App\Notifications;

use App\Models\TransportCancellationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CancellationRejectedNotification extends Notification
{
    use Queueable;
    public function __construct(public TransportCancellationRequest $request) {}
    public function via($notifiable): array { return ['database']; }
    public function toArray($notifiable): array
    {
        return [
            'type' => 'transport_cancellation_rejected',
            'title' => 'Cancellation Request Rejected',
            'message' => 'Your cancellation request for ' . ($this->request->booking->booking_ref ?? '') . ' was rejected. Reason: ' . ($this->request->provider_response ?? 'Not specified'),
            'url' => url('/traveler/transport/bookings/' . $this->request->transport_booking_id),
            'booking_id' => $this->request->transport_booking_id,
        ];
    }
}