<?php

namespace App\Notifications;

use App\Models\SosAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RescueDispatchNotification extends Notification
{
    use Queueable;

    protected $sos;

    public function __construct(SosAlert $sos)
    {
        $this->sos = $sos;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rescue_dispatch',
            'sos_id' => $this->sos->id,
            'message' => "Emergency SOS alert #" . $this->sos->id . " in your area.",
            'url' => route('provider.rescue.sos'),
            'created_at' => now()->toIso8601String(),
        ];
    }
}