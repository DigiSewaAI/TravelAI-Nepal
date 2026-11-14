<?php

namespace App\Notifications;

use App\Models\HotelBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewHotelBookingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public HotelBooking $booking
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'hotel_booking',
            'title' => 'New Hotel Booking',
            'message' => $this->booking->guest_name . ' booked Room ' . ($this->booking->room->room_number ?? '') . ' for ' . $this->booking->nights . ' nights.',
            'url' => route('provider.hotel.bookings'),
            'booking_id' => $this->booking->id,
        ];
    }
}