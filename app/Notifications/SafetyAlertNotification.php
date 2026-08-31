<?php

namespace App\Notifications;

use App\Models\TravelerSafetyAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SafetyAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $alert;

    public function __construct(TravelerSafetyAlert $alert)
    {
        $this->alert = $alert;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $incident = $this->alert->incident;
        $severityColor = $this->getSeverityColor($incident->severity);
        $statusLabel = $this->getStatusLabel($incident->severity);

        return (new MailMessage)
            ->subject("{$severityColor} Travel Safety Alert: {$incident->title}")
            ->greeting("Hello {$notifiable->name}!")
            ->line(new HtmlString("<strong>{$statusLabel}: {$incident->title}</strong>"))
            ->line($this->alert->message)
            ->line("**Location:** {$incident->location_name}")
            ->line("**Type:** {$incident->incident_type}")
            ->line("**Reported:** {$incident->reported_at->format('F j, Y, g:i A')}")
            ->line("**Last Verified:** " . ($incident->last_verified_at ? $incident->last_verified_at->format('F j, Y, g:i A') : 'Not verified'))
            ->when($incident->recommended_action, function ($mail) use ($incident) {
                return $mail->line("**Recommendation:** {$incident->recommended_action}");
            })
            ->action('View Safety Details', url("/travel-safety/incident/{$incident->id}"))
            ->line('Please review your travel plans and stay safe.')
            ->line('For emergency assistance, contact local authorities.');
    }

    public function toArray($notifiable)
    {
        return [
            'alert_id' => $this->alert->id,
            'incident_id' => $this->alert->incident_id,
            'title' => $this->alert->incident->title,
            'message' => $this->alert->message,
            'severity' => $this->alert->incident->severity,
            'location' => $this->alert->incident->location_name,
            'sent_at' => $this->alert->sent_at,
        ];
    }

    protected function getSeverityColor(?string $severity): string
    {
        return match ($severity) {
            'critical' => '🔴',
            'high' => '🟠',
            'moderate' => '🟡',
            default => '🟢',
        };
    }

    protected function getStatusLabel(?string $severity): string
    {
        return match ($severity) {
            'critical' => '🔴 CRITICAL ALERT',
            'high' => '🟠 HIGH RISK',
            'moderate' => '🟡 CAUTION',
            default => '🟢 ADVISORY',
        };
    }
}