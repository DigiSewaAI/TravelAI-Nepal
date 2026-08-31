<?php

namespace App\Services\JourneyReplay;

use App\Models\User;
use App\Models\QrScan;
use App\Models\UserMedia;
use App\Services\Media\FallbackMediaService;
use App\Services\Passport\DigitalTrekPassportService;
use Illuminate\Support\Collection;

class CinematicReplayService
{
    protected $fallbackMedia;
    protected $passportService;

    public function __construct(
        FallbackMediaService $fallbackMedia,
        DigitalTrekPassportService $passportService
    ) {
        $this->fallbackMedia = $fallbackMedia;
        $this->passportService = $passportService;
    }

    public function getScenes(User $user): array
    {
        $scans = QrScan::whereHas('booking', function($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })->with(['waypoint', 'booking'])
          ->orderBy('scanned_at', 'asc')
          ->get();

        if ($scans->isEmpty()) {
            return [];
        }

        $grouped = $scans->groupBy('waypoint_id');
        $scenes = [];
        $index = 0;

        foreach ($grouped as $waypointId => $group) {
            $firstScan = $group->first();
            $waypoint = $firstScan->waypoint;
            if (!$waypoint) continue;

            $media = UserMedia::where('user_id', $user->id)
                              ->where('waypoint_id', $waypointId)
                              ->orderBy('is_primary', 'desc')
                              ->get();

            $mediaItems = [];
            if ($media->isEmpty()) {
                $fallbackUrl = $this->fallbackMedia->getImageForDestination($waypoint->name);
                if ($fallbackUrl) {
                    $mediaItems[] = [
                        'type' => 'image',
                        'url' => $fallbackUrl,
                        'source' => 'fallback',
                    ];
                }
            } else {
                foreach ($media as $item) {
                    $mediaItems[] = [
                        'type' => $item->media_type,
                        'url' => asset('storage/' . $item->optimized_path),
                        'thumbnail' => $item->thumbnail_path ? asset('storage/' . $item->thumbnail_path) : null,
                        'source' => 'user',
                    ];
                }
            }

            $scenes[] = [
                'checkpoint' => $waypoint->name,
                'altitude' => $waypoint->altitude,
                'latitude' => $waypoint->latitude,
                'longitude' => $waypoint->longitude,
                'scanned_at' => $firstScan->scanned_at,
                'media' => $mediaItems,
                'index' => ++$index,
            ];
        }

        return $scenes;
    }

    public function getCinematicData(User $user): array
    {
        $scenes = $this->getScenes($user);
        
        // Use passport service for reliable stats (keys: total_treks, total_checkins, unique_waypoints, highest_altitude, etc.)
        $stats = $this->passportService->getStatistics($user);

        // Compute journey start/end from scenes
        $journeyStart = null;
        $journeyEnd = null;
        if (!empty($scenes)) {
            $first = $scenes[0]['scanned_at'] ?? null;
            $last = $scenes[count($scenes)-1]['scanned_at'] ?? null;
            if ($first) $journeyStart = $first;
            if ($last) $journeyEnd = $last;
        }

        return [
            'scenes' => $scenes,
            'stats' => [
                'total_bookings' => $stats['total_treks'] ?? 0,
                'total_checkins' => $stats['total_checkins'] ?? 0,
                'unique_places' => $stats['unique_waypoints'] ?? 0,
                'highest_altitude' => $stats['highest_altitude'] ?? 0,
                'journey_start' => $journeyStart,
                'journey_end' => $journeyEnd,
            ],
        ];
    }
}