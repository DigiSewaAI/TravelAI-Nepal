<?php

namespace App\Jobs;

use App\Models\SosAlert;
use App\Mail\SosAlertMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSosNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(public SosAlert $sos) {}

    public function handle(): void
    {
        $this->sos->load(['traveler', 'provider', 'booking.service']);

        $recipients = [];

        // Primary: provider contact email
        if ($this->sos->provider && $this->sos->provider->contact_email) {
            $recipients[] = $this->sos->provider->contact_email;
        }

        // Fallback: platform admin email (from config)
        $adminEmail = config('mail.from.address');
        if ($adminEmail && !in_array($adminEmail, $recipients, true)) {
            $recipients[] = $adminEmail;
        }

        if (empty($recipients)) {
            Log::warning('SOS email: no recipients available', ['sos_id' => $this->sos->id]);
            return;
        }

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new SosAlertMail($this->sos));
            } catch (\Throwable $e) {
                Log::error('SOS email failed', [
                    'sos_id' => $this->sos->id,
                    'email'  => $email,
                    'error'  => $e->getMessage(),
                ]);
            }
        }
    }
}