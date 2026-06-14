<?php

namespace App\Jobs;

use App\Models\SosAlert;
use App\Models\Agency;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSosNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $sos;

    public function __construct(SosAlert $sos)
    {
        $this->sos = $sos;
    }

    public function handle()
    {
        // Find the agency that owns this trek (via booking->trek)
        $agency = $this->sos->booking->trek->agency;
        if (!$agency || !$agency->email) {
            return;
        }

        $trekker = $this->sos->trekker;
        $booking = $this->sos->booking;
        $trek = $booking->trek;

        $data = [
            'agency_name' => $agency->name,
            'trekker_name' => $trekker->name,
            'trek_name' => $trek->name,
            'message' => $this->sos->message ?? 'No additional message.',
            'latitude' => $this->sos->latitude,
            'longitude' => $this->sos->longitude,
            'google_maps_link' => "https://www.google.com/maps?q={$this->sos->latitude},{$this->sos->longitude}",
            'alert_time' => $this->sos->created_at->format('Y-m-d H:i:s'),
        ];

        // Send email
        Mail::send('emails.sos_alert', $data, function ($message) use ($agency) {
            $message->to($agency->email, $agency->name)
                    ->subject('🚨 SOS Alert – Immediate Action Required');
        });

        // Optional: Send SMS via Twilio/Nexmo here
    }
}