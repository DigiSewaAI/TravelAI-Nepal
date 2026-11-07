<?php

namespace App\Mail;

use App\Models\IndividualBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IndividualBookingCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public IndividualBooking $booking;

    public function __construct(IndividualBooking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject('Your TravelAI Nepal Booking ID: #' . $this->booking->id)
            ->view('emails.individual-booking-created')
            ->with([
                'booking' => $this->booking,
                'claimUrl' => route('traveler.individual.claim.form'),
                'loginUrl' => url('/login'),
                'registerUrl' => url('/register'),
                'isRegistered' => \App\Models\User::where('email', $this->booking->customer_email)->exists(),
            ]);
    }
}