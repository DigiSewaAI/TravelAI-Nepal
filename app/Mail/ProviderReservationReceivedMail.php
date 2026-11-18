<?php

namespace App\Mail;

use App\Models\RestaurantReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProviderReservationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    public function __construct(RestaurantReservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function build()
    {
        return $this->subject('New Table Reservation ✅ — TravelAI Nepal')
                    ->view('emails.provider-reservation-received');
    }
}