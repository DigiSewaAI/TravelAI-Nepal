<?php

namespace App\Services\Safety;

use App\Models\TravelSafetyIncident;
use App\Models\TravelerSafetyAlert;
use App\Models\User;
use App\Models\Booking;
use App\Models\PlannerResult;
use App\Models\ItineraryDay;
use App\Models\Waypoint;
use App\Models\Trek;
use App\Models\Route;
use App\Notifications\SafetyAlertNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlertService
{
    protected $dedupWindow;
    protected $resendOnSeverityChange;

    public function __construct()
    {
        $this->dedupWindow = config('safety.alert.dedup_window_minutes', 60);
        $this->resendOnSeverityChange = config('safety.alert.resend_on_severity_change', true);
    }

    /**
     * Check all travelers for affected itineraries
     */
    public function checkAllTravelers(TravelSafetyIncident $incident): array
    {
        $affectedUsers = [];
        $alertsCreated = [];

        // 1. Check active bookings
        $affectedBookings = $this->findAffectedBookings($incident);
        foreach ($affectedBookings as $booking) {
            if ($this->shouldSendAlert($booking->user_id, $incident)) {
                $alert = $this->createAlert($booking->user, $incident, $booking, 'booking');
                $alertsCreated[] = $alert;
                $affectedUsers[] = $booking->user_id;
            }
        }

        // 2. Check planner itineraries (AI generated)
        $affectedPlannerResults = $this->findAffectedPlannerResults($incident);
        foreach ($affectedPlannerResults as $result) {
            if ($result->plannerRequest && $result->plannerRequest->user_id) {
                $userId = $result->plannerRequest->user_id;
                if ($this->shouldSendAlert($userId, $incident)) {
                    $user = User::find($userId);
                    if ($user) {
                        $alert = $this->createAlert($user, $incident, $result, 'itinerary');
                        $alertsCreated[] = $alert;
                        $affectedUsers[] = $userId;
                    }
                }
            }
        }

        // 3. Check Digital Trek Passport check-ins (current location)
        $affectedCheckins = $this->findAffectedCheckins($incident);
        foreach ($affectedCheckins as $scan) {
            if ($scan->booking && $scan->booking->traveler_id) {
                $userId = $scan->booking->traveler_id;
                if ($this->shouldSendAlert($userId, $incident)) {
                    $user = User::find($userId);
                    if ($user) {
                        $alert = $this->createAlert($user, $incident, $scan, 'checkpoint');
                        $alertsCreated[] = $alert;
                        $affectedUsers[] = $userId;
                    }
                }
            }
        }

        // 4. Dispatch notifications for created alerts
        foreach ($alertsCreated as $alert) {
            $this->sendNotification($alert);
        }

        Log::info('Alert check completed', [
            'incident_id' => $incident->id,
            'affected_users' => count(array_unique($affectedUsers)),
            'alerts_created' => count($alertsCreated),
        ]);

        return [
            'affected_users' => array_unique($affectedUsers),
            'alerts' => $alertsCreated,
        ];
    }

    /**
     * Find bookings affected by an incident
     */
    protected function findAffectedBookings(TravelSafetyIncident $incident): array
    {
        $bookings = [];

        // Get all active bookings
        $allBookings = Booking::with(['service', 'traveler'])
            ->whereIn('status', ['confirmed', 'active'])
            ->where('start_date', '>=', now())
            ->get();

        foreach ($allBookings as $booking) {
            if ($this->isBookingAffected($booking, $incident)) {
                $bookings[] = $booking;
            }
        }

        return $bookings;
    }

    /**
     * Check if a booking is affected by an incident
     */
    protected function isBookingAffected(Booking $booking, TravelSafetyIncident $incident): bool
    {
        // Check if booking's trek matches incident
        if ($booking->service) {
            $trek = $booking->service->trek;
            if ($trek) {
                $incidentTreks = $incident->treks()->pluck('treks.id')->toArray();
                if (in_array($trek->id, $incidentTreks)) {
                    return true;
                }
            }
        }

        // Check if booking's location matches incident
        if ($booking->service && $booking->service->location) {
            $location = $booking->service->location;
            $incidentLocations = $incident->locations()->pluck('locations.id')->toArray();
            if (in_array($location->id, $incidentLocations)) {
                return true;
            }

            // Check distance to incident
            if ($incident->latitude && $incident->longitude && $location->latitude && $location->longitude) {
                $distance = $this->calculateDistance(
                    $incident->latitude,
                    $incident->longitude,
                    $location->latitude,
                    $location->longitude
                );
                $radius = $incident->affected_radius ?? config('safety.matching.default_radius', 5000);
                if ($distance <= $radius) {
                    return true;
                }
            }
        }

        // Check if any waypoint in booking's route is affected
        if ($booking->service && $booking->service->route) {
            $route = $booking->service->route;
            $incidentWaypoints = $incident->waypoints()->pluck('waypoints.id')->toArray();
            foreach ($route->segments as $segment) {
                if (in_array($segment->from_waypoint_id, $incidentWaypoints) ||
                    in_array($segment->to_waypoint_id, $incidentWaypoints)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Find affected planner results (AI itineraries)
     */
    protected function findAffectedPlannerResults(TravelSafetyIncident $incident): array
    {
        $results = [];

        $plannerResults = PlannerResult::with(['plannerRequest', 'days'])
            ->whereHas('plannerRequest', function ($q) {
                $q->whereNotNull('user_id');
            })
            ->latest()
            ->limit(100)
            ->get();

        foreach ($plannerResults as $result) {
            if ($this->isPlannerResultAffected($result, $incident)) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Check if a planner result is affected
     */
    protected function isPlannerResultAffected(PlannerResult $result, TravelSafetyIncident $incident): bool
    {
        $incidentWaypoints = $incident->waypoints()->pluck('waypoints.id')->toArray();

        foreach ($result->days as $day) {
            // Check overnight waypoint
            if ($day->overnight_waypoint_id && in_array($day->overnight_waypoint_id, $incidentWaypoints)) {
                return true;
            }

            // Check if any item has service affected
            foreach ($day->items as $item) {
                if ($item->service_id) {
                    $service = $item->service;
                    if ($service && $service->location) {
                        // Check if service location is affected
                        $incidentLocations = $incident->locations()->pluck('locations.id')->toArray();
                        if (in_array($service->location->id, $incidentLocations)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Find affected check-ins (Digital Trek Passport)
     */
    protected function findAffectedCheckins(TravelSafetyIncident $incident): array
    {
        $scans = [];

        // Get recent check-ins (last 7 days)
        $recentScans = \App\Models\QrScan::with(['booking', 'waypoint'])
            ->where('scanned_at', '>=', now()->subDays(7))
            ->orderBy('scanned_at', 'desc')
            ->limit(100)
            ->get();

        $incidentWaypoints = $incident->waypoints()->pluck('waypoints.id')->toArray();

        foreach ($recentScans as $scan) {
            if ($scan->waypoint_id && in_array($scan->waypoint_id, $incidentWaypoints)) {
                $scans[] = $scan;
            } elseif ($scan->waypoint && $incident->latitude && $incident->longitude) {
                $distance = $this->calculateDistance(
                    $incident->latitude,
                    $incident->longitude,
                    $scan->waypoint->latitude,
                    $scan->waypoint->longitude
                );
                $radius = $incident->affected_radius ?? config('safety.matching.default_radius', 5000);
                if ($distance <= $radius) {
                    $scans[] = $scan;
                }
            }
        }

        return $scans;
    }

    /**
     * Check if alert should be sent (deduplication)
     */
    protected function shouldSendAlert(int $userId, TravelSafetyIncident $incident): bool
    {
        // Check if already sent within dedup window
        $existing = TravelerSafetyAlert::where('user_id', $userId)
            ->where('incident_id', $incident->id)
            ->where('sent_at', '>=', now()->subMinutes($this->dedupWindow))
            ->first();

        if ($existing) {
            // If severity changed and resend is enabled, send again
            if ($this->resendOnSeverityChange) {
                $incident->refresh();
                if ($existing->severity !== $incident->severity) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Create a traveler safety alert
     */
    protected function createAlert(User $user, TravelSafetyIncident $incident, $affectable, string $type): TravelerSafetyAlert
    {
        $alert = TravelerSafetyAlert::create([
            'user_id' => $user->id,
            'incident_id' => $incident->id,
            'affectable_type' => get_class($affectable),
            'affectable_id' => $affectable->id,
            'alert_type' => $type,
            'severity' => $incident->severity,
            'sent_at' => now(),
            'delivery_channel' => 'database',
            'message' => $this->generateAlertMessage($incident, $affectable, $type),
            'metadata' => [
                'incident_title' => $incident->title,
                'location' => $incident->location_name,
                'affected_entity' => $affectable->id,
                'entity_type' => $type,
            ],
        ]);

        Log::info('Traveler safety alert created', [
            'alert_id' => $alert->id,
            'user_id' => $user->id,
            'incident_id' => $incident->id,
            'type' => $type,
        ]);

        return $alert;
    }

    /**
     * Generate human-readable alert message
     */
    protected function generateAlertMessage(TravelSafetyIncident $incident, $affectable, string $type): string
    {
        $status = $this->getStatusLabel($incident->severity);
        $location = $incident->location_name ?? 'the affected area';

        $messages = [
            'booking' => "🚨 {$status}: Your upcoming booking may be affected by a {$incident->incident_type} near {$location}. Please review your travel plans.",
            'itinerary' => "🚨 {$status}: Your AI-generated itinerary includes locations near a {$incident->incident_type} near {$location}. Consider adjusting your plans.",
            'checkpoint' => "🚨 {$status}: You've recently been near {$location} where a {$incident->incident_type} has been reported. Please stay informed.",
        ];

        return $messages[$type] ?? "🚨 Travel Safety Alert: {$incident->title} near {$location}.";
    }

    /**
     * Get status label
     */
    protected function getStatusLabel(?string $severity): string
    {
        $labels = [
            'critical' => '🔴 CRITICAL',
            'high' => '🟠 HIGH RISK',
            'moderate' => '🟡 CAUTION',
            'low' => '🟢 ADVISORY',
        ];
        return $labels[$severity] ?? '⚠️ ALERT';
    }

    /**
     * Send notification for an alert
     */
    protected function sendNotification(TravelerSafetyAlert $alert): void
    {
        try {
            $user = $alert->user;
            if (!$user) {
                return;
            }

            // Send via Laravel notification system
            $user->notify(new SafetyAlertNotification($alert));

            // Update delivery channel
            $alert->delivery_channel = 'database,mail';
            $alert->save();

            Log::info('Safety alert notification sent', [
                'alert_id' => $alert->id,
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send safety alert notification', [
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mark alert as read
     */
    public function markAsRead(int $alertId, int $userId): bool
    {
        $alert = TravelerSafetyAlert::where('id', $alertId)
            ->where('user_id', $userId)
            ->first();

        if (!$alert) {
            return false;
        }

        $alert->read_at = now();
        $alert->save();

        return true;
    }

    /**
     * Get unread alerts for a user
     */
    public function getUnreadAlerts(int $userId): array
    {
        return TravelerSafetyAlert::where('user_id', $userId)
            ->whereNull('read_at')
            ->with('incident')
            ->orderBy('sent_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Calculate distance between two coordinates (meters)
     */
    protected function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}