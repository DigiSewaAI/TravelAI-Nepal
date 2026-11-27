<?php

namespace App\Notifications;

use App\Models\IndividualBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewIndividualBookingNotification extends Notification
{
    use Queueable;

    public function __construct(public IndividualBooking $booking) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'individual_booking_new',
            'title'    => 'New Guide Booking',
            'message'  => $this->booking->client_name . ' booked ' . ($this->booking->title ?? 'your guide service') . ' for ' . $this->booking->start_date?->format('M d, Y'),
            'url'      => url('/provider/individual/bookings/' . $this->booking->id),
            'booking_id' => $this->booking->id,
        ];
    }
}