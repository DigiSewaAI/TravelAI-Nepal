<?php

namespace App\Notifications;

use App\Models\RestaurantReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReservationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public RestaurantReservation $reservation
    ) {}

    public function via($notifiable): array
    {
        return ['database']; // बेल आइकनको लागि मात्र
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'restaurant_reservation',
            'title' => 'New Table Reservation',
            'message' => $this->reservation->customer_name . ' requested a table for ' . $this->reservation->guests . ' guests on ' . $this->reservation->reservation_date->format('M d, Y') . ' at ' . $this->reservation->reservation_time,
            'url' => route('provider.restaurant.reservations'),
            'reservation_id' => $this->reservation->id,
        ];
    }
}