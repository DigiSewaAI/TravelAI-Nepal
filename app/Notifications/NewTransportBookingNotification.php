<?php

namespace App\Notifications;

use App\Models\TransportBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewTransportBookingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public TransportBooking $booking
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

     public function toArray($notifiable): array
    {
        $date = $this->booking->travel_date
            ? $this->booking->travel_date->format('M d, Y')
            : '—';
        $time = $this->booking->travel_time ? ' at ' . $this->booking->travel_time : '';

        // Safe URL — route missing भए fallback
        try {
            $url = route('provider.transport.bookings.show', $this->booking->id);
        } catch (\Throwable $e) {
            $url = url('/provider/transport/bookings/' . $this->booking->id);
        }

        return [
            'type' => 'transport_booking',
            'title' => 'New Transport Booking',
            'message' => ($this->booking->passenger_name ?? 'Passenger')
                . ' booked ' . ($this->booking->route_name ?? 'a route')
                . ' on ' . $date . $time
                . ' (' . ($this->booking->vehicle->vehicle_number ?? 'vehicle') . ')',
            'url' => $url,
            'booking_id' => $this->booking->id,
            'booking_ref' => $this->booking->booking_ref,
        ];
    }
}