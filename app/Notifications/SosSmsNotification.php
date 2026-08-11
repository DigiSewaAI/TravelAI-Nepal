<?php

namespace App\Notifications;

use App\Models\SosAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SosSmsNotification extends Notification
{
    use Queueable;

    protected $sos;

    public function __construct(SosAlert $sos)
    {
        $this->sos = $sos;
    }

    public function via($notifiable)
    {
        return ['mail']; // Add 'sms' if SMS gateway is configured
    }

    public function toMail($notifiable)
    {
        $booking = $this->sos->booking;
        $service = $booking?->service;
        $provider = $service?->provider;

        return (new MailMessage)
            ->subject('🚨 SOS Alert! Emergency Assistance Needed')
            ->markdown('emails.sos.sms', [
                'sos' => $this->sos,
                'booking' => $booking,
                'service' => $service,
                'provider' => $provider,
                'location' => $this->sos->latitude . ', ' . $this->sos->longitude,
                'mapUrl' => 'https://www.google.com/maps?q=' . $this->sos->latitude . ',' . $this->sos->longitude,
            ]);
    }

    /**
     * Send SMS via SMS gateway (Twilio, Nepal SMS, etc.)
     * If gateway is 'log' or empty, just log the message.
     */
    public function sendSms($to, $message): bool
    {
        // 🔥 Check gateway – skip real SMS if not configured
        $gateway = config('services.sms.gateway', 'log'); // default 'log'

        // If gateway is 'log' or empty, just log and return true
        if (empty($gateway) || $gateway === 'log') {
            Log::info('📱 SMS would be sent (skip mode)', [
                'to' => $to,
                'message' => $message,
                'gateway' => $gateway,
            ]);
            return true; // pretend success
        }

        // Real SMS sending
        try {
            if ($gateway === 'twilio') {
                return $this->sendViaTwilio($to, $message);
            } elseif ($gateway === 'nepal_sms') {
                return $this->sendViaNepalSms($to, $message);
            }

            Log::warning('Unknown SMS gateway: ' . $gateway);
            return false;

        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function sendViaTwilio($to, $message): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post('https://api.twilio.com/2010-04-01/Accounts/' . $sid . '/Messages.json', [
                'To' => $to,
                'From' => $from,
                'Body' => $message,
            ]);

        return $response->successful();
    }

    protected function sendViaNepalSms($to, $message): bool
    {
        // Nepal SMS Gateway implementation
        $apiKey = config('services.nepal_sms.api_key');
        $senderId = config('services.nepal_sms.sender_id');

        $response = Http::post('https://api.nepalsms.com/v1/send', [
            'api_key' => $apiKey,
            'sender_id' => $senderId,
            'mobile' => $to,
            'message' => $message,
        ]);

        return $response->successful();
    }

    public function toArray($notifiable)
    {
        return [
            'sos_id' => $this->sos->id,
            'message' => 'SOS alert triggered',
            'location' => $this->sos->latitude . ', ' . $this->sos->longitude,
        ];
    }
}