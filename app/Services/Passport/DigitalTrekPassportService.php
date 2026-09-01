<?php

namespace App\Services\Passport;

use App\Models\User;
use App\Models\Booking;
use App\Models\QrScan;
use App\Models\Waypoint;
use App\Services\Safety\SafetyStatusService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DigitalTrekPassportService
{
    protected $safetyStatusService;

    /**
     * Constructor with SafetyStatusService injection
     */
    public function __construct(SafetyStatusService $safetyStatusService)
    {
        $this->safetyStatusService = $safetyStatusService;
    }

    /**
     * Get full passport data for a user
     */
    public function getPassportData(User $user): array
    {
        return [
            'profile' => $this->getProfile($user),
            'statistics' => $this->getStatistics($user),
            'active_journey' => $this->getActiveJourney($user),
            'journeys' => $this->getTrekJourneys($user),
            'stamps' => $this->getStamps($user),
            'timeline' => $this->getTimeline($user),
            'map_points' => $this->getMapPoints($user),
        ];
    }

    /**
     * Get user profile info for passport
     */
    public function getProfile(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'member_since' => $user->created_at,
            'public_id' => $user->passport_public_id,
            'privacy' => $user->passport_privacy ?? 'private',
        ];
    }

    /**
     * Calculate statistics
     */
    public function getStatistics(User $user): array
    {
        // Total treks (bookings)
        $totalBookings = Booking::where('traveler_id', $user->id)->count();
        $completedBookings = Booking::where('traveler_id', $user->id)
                                    ->where('status', 'completed')
                                    ->count();
        $activeBookings = Booking::where('traveler_id', $user->id)
                                 ->whereIn('status', ['confirmed', 'active'])
                                 ->count();

        // Check-in stats with duplicate filter
        $stats = QrScan::whereHas('booking', function ($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })
        ->whereNull('duplicate_of')
        ->join('waypoints', 'qr_scans.waypoint_id', '=', 'waypoints.id')
        ->selectRaw('
            COUNT(*) as total_scans,
            COUNT(DISTINCT qr_scans.waypoint_id) as unique_waypoints,
            MAX(waypoints.altitude) as highest_altitude,
            MIN(waypoints.altitude) as lowest_altitude,
            AVG(waypoints.altitude) as avg_altitude
        ')
        ->first();

        return [
            'total_treks' => $totalBookings,
            'completed_treks' => $completedBookings,
            'active_treks' => $activeBookings,
            'total_checkins' => $stats->total_scans ?? 0,
            'unique_waypoints' => $stats->unique_waypoints ?? 0,
            'highest_altitude' => $stats->highest_altitude ?? 0,
            'lowest_altitude' => $stats->lowest_altitude ?? 0,
            'avg_altitude' => round($stats->avg_altitude ?? 0),
        ];
    }

    /**
     * Get active journey (first confirmed/active booking)
     */
    public function getActiveJourney(User $user): ?array
    {
        $booking = Booking::where('traveler_id', $user->id)
                          ->whereIn('status', ['confirmed', 'active'])
                          ->with(['service', 'qrScans.waypoint'])
                          ->first();

        if (!$booking) {
            return null;
        }

        $totalWaypoints = $booking->qrScans->filter(function ($scan) {
            return !is_null($scan->waypoint_id);
        })->count();

        return [
            'booking' => $booking,
            'name' => $booking->service->name ?? 'Untitled Trek',
            'start_date' => $booking->start_date,
            'status' => $booking->status,
            'checkins' => $booking->qrScans->count(),
            'unique_waypoints' => $booking->qrScans->unique('waypoint_id')->count(),
            'last_checkin' => $booking->qrScans->last(),
            'progress' => $totalWaypoints > 0 ? min(100, $totalWaypoints * 10) : 0,
        ];
    }

    /**
     * Get all trek journeys (grouped by booking)
     */
    public function getTrekJourneys(User $user): Collection
    {
        return Booking::where('traveler_id', $user->id)
                      ->with(['service', 'qrScans.waypoint'])
                      ->latest()
                      ->get()
                      ->map(function ($booking) {
                          return [
                              'booking_id' => $booking->id,
                              'name' => $booking->service->name ?? 'Untitled Trek',
                              'start_date' => $booking->start_date,
                              'status' => $booking->status,
                              'checkins' => $booking->qrScans->count(),
                              'unique_waypoints' => $booking->qrScans->unique('waypoint_id')->count(),
                              'stamps' => $booking->qrScans,
                          ];
                      });
    }

    /**
     * Get all stamps (with waypoint data) – duplicates excluded
     */
    public function getStamps(User $user): Collection
    {
        return QrScan::whereHas('booking', function ($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })
        ->with(['waypoint', 'booking.service'])
        ->whereNotNull('waypoint_id')
        ->whereNull('duplicate_of')
        ->latest('scanned_at')
        ->get()
        ->map(function ($scan) {
            return [
                'scan' => $scan,
                'waypoint' => $scan->waypoint,
                'service' => $scan->booking->service,
                'date' => $scan->scanned_at,
                'location' => $scan->waypoint->name ?? $scan->checkpoint_name,
                'altitude' => $scan->waypoint->altitude ?? null,
                'type' => $scan->waypoint->type ?? null,
            ];
        });
    }

    /**
     * Get chronological timeline – duplicates excluded
     */
    public function getTimeline(User $user): Collection
    {
        return QrScan::whereHas('booking', function ($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })
        ->with(['waypoint', 'booking.service'])
        ->whereNull('duplicate_of')
        ->orderBy('scanned_at', 'desc')
        ->get();
    }

    /**
     * Get map points (visited waypoints with coordinates) – duplicates excluded
     */
    public function getMapPoints(User $user): Collection
    {
        return QrScan::whereHas('booking', function ($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })
        ->with('waypoint')
        ->whereNotNull('waypoint_id')
        ->whereNull('duplicate_of')
        ->get()
        ->map(function ($scan) {
            return [
                'id' => $scan->id,
                'name' => $scan->waypoint->name ?? $scan->checkpoint_name,
                'type' => $scan->waypoint->type ?? 'checkpoint',
                'latitude' => $scan->waypoint->latitude ?? $scan->latitude,
                'longitude' => $scan->waypoint->longitude ?? $scan->longitude,
                'altitude' => $scan->waypoint->altitude ?? null,
                'visited_at' => $scan->scanned_at,
                'booking_id' => $scan->booking_id,
            ];
        })
        ->unique('id');
    }

    /**
     * Get total XP for user (check-ins + achievements)
     */
    public function getTotalXP(User $user): int
    {
        $checkinXP = QrScan::whereHas('booking', function ($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })->count() * 10;

        $achievementXP = app(\App\Services\Passport\AchievementService::class)->getTotalXP($user);

        return $checkinXP + $achievementXP;
    }

    /**
     * Calculate trekker level based on unique waypoints
     */
    public function calculateLevel(User $user): int
    {
        $unique = QrScan::whereHas('booking', function ($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })
        ->whereNotNull('waypoint_id')
        ->distinct('waypoint_id')
        ->count('waypoint_id');

        return match(true) {
            $unique >= 50 => 5,
            $unique >= 30 => 4,
            $unique >= 15 => 3,
            $unique >= 5  => 2,
            $unique >= 1  => 1,
            default => 0,
        };
    }

    // ============================================================
    // ✅ NEW: Safety Status Methods (Phase 5)
    // ============================================================

    /**
     * Get safety status for a checkpoint (waypoint)
     */
    public function getCheckpointSafetyStatus(Waypoint $waypoint): array
    {
        return $this->safetyStatusService->getStatusForEntity($waypoint);
    }

    /**
     * Get safety status for all checkpoints in a booking
     */
    public function getBookingCheckpointsSafety(Booking $booking): Collection
    {
        $waypoints = $booking->service?->route?->segments()
            ->with('fromWaypoint', 'toWaypoint')
            ->get()
            ->flatMap(function ($segment) {
                return [$segment->fromWaypoint, $segment->toWaypoint];
            })
            ->unique('id')
            ->filter();

        return $waypoints->map(function ($waypoint) {
            $status = $this->getCheckpointSafetyStatus($waypoint);
            return [
                'waypoint' => $waypoint,
                'status' => $status['status'],
                'status_color' => $status['status_color'] ?? '⚪',
                'incident' => $status['incident'] ? [
                    'id' => $status['incident']->id,
                    'title' => $status['incident']->title,
                    'severity' => $status['incident']->severity,
                ] : null,
            ];
        });
    }
}