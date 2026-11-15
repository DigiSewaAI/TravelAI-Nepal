<?php
namespace App\Notifications;

use App\Models\TransportBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CancellationRequestedNotification extends Notification
{
    use Queueable;
    public function __construct(public TransportBooking $booking) {}
    public function via($notifiable): array { return ['database']; }
    public function toArray($notifiable): array
    {
        try { $url = route('provider.transport.cancellations'); }
        catch (\Throwable $e) { $url = url('/provider/transport/cancellations'); }
        return [
            'type' => 'transport_cancellation_request',
            'title' => 'Cancellation Request',
            'message' => ($this->booking->passenger_name ?? 'Customer') . ' requested to cancel ' . ($this->booking->booking_ref ?? '') . ' — ' . ($this->booking->route_name ?? ''),
            'url' => $url,
            'booking_id' => $this->booking->id,
        ];
    }
}