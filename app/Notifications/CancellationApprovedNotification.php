<?php
namespace App\Notifications;

use App\Models\TransportBooking;
use App\Models\TransportCancellationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CancellationApprovedNotification extends Notification
{
    use Queueable;
    public function __construct(
        public TransportBooking $booking,
        public TransportCancellationRequest $request
    ) {}
    public function via($notifiable): array { return ['database']; }
    public function toArray($notifiable): array
    {
        $refundMsg = $this->request->refund_status === 'pending'
            ? ' Refund of Rs ' . number_format((float) $this->request->estimated_refund, 2) . ' will be processed.'
            : '';
        return [
            'type' => 'transport_cancellation_approved',
            'title' => 'Cancellation Approved',
            'message' => 'Your booking ' . ($this->booking->booking_ref ?? '') . ' has been cancelled.' . $refundMsg,
            'url' => url('/traveler/transport/bookings/' . $this->booking->id),
            'booking_id' => $this->booking->id,
        ];
    }
}