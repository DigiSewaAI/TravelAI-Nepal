<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\UserMedia;
use Illuminate\Http\Request;

class JourneyReplayController extends Controller
{
    public function show($token)
    {
        $booking = Booking::findByShareToken($token);

        if (!$booking || $booking->visibility === 'private') {
            abort(404, 'Journey not found or not shared.');
        }

        // ✅ FIXED: qrScans() instead of checkins()
        $qrScans = $booking->qrScans()->with('waypoint')->get();
        $waypoints = $qrScans->pluck('waypoint')->filter()->unique('id');

        // ✅ FIXED: traveler_id instead of user_id
        $media = UserMedia::whereIn('waypoint_id', $waypoints->pluck('id'))
                          ->where('user_id', $booking->traveler_id)
                          ->get();

        $stats = [
            'total_places'      => $waypoints->count(),
            'total_moments'     => $media->count(),
            'highest_altitude'  => $waypoints->max('altitude') ?? 0,
            'journey_start'     => $booking->start_date,
            'journey_end'       => $booking->end_date ?? $booking->start_date,
        ];

        $replayData = [
            'booking'     => $booking,
            'checkpoints' => $waypoints,
            'media'       => $media,
            'stats'       => $stats,
            'share_token' => $token,
        ];

        return view('public.journey-replay', compact('replayData', 'booking'));
    }

    public function serveMedia($token, $filename)
    {
        $booking = Booking::findByShareToken($token);
        if (!$booking || $booking->visibility === 'private') {
            abort(403);
        }

        $media = UserMedia::where('file_name', $filename)
                          ->whereHas('waypoint', function ($q) use ($booking) {
                              $q->whereIn('id', $booking->qrScans()->pluck('waypoint_id'));
                          })->first();

        if (!$media) {
            abort(404);
        }

        $path = storage_path('app/public/' . $media->optimized_path);
        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

        public function cinematic($token)
    {
        $booking = Booking::findByShareToken($token);
        if (!$booking || $booking->visibility === 'private') {
            abort(404);
        }

        // PHASE-5D-FIX: build booking-scoped scenes for public cinematic
        // (mirrors show() logic — reuse traveler/cinematic-replay blade)
        $scans = $booking->qrScans()
            ->with('waypoint')
            ->orderBy('scanned_at', 'asc')
            ->get();

        $grouped = $scans->groupBy('waypoint_id');
        $scenes = [];
        $index = 0;

        foreach ($grouped as $waypointId => $group) {
            $firstScan = $group->first();
            $waypoint = $firstScan->waypoint;
            if (!$waypoint) continue;

            $mediaItems = UserMedia::where('user_id', $booking->traveler_id)
                ->where('waypoint_id', $waypointId)
                ->orderBy('is_primary', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'type'      => $item->media_type,
                        'url'       => asset('storage/' . $item->optimized_path),
                        'thumbnail' => $item->thumbnail_path ? asset('storage/' . $item->thumbnail_path) : null,
                        'source'    => 'user',
                    ];
                })
                ->toArray();

            $scenes[] = [
                'checkpoint' => $waypoint->name,
                'altitude'   => $waypoint->altitude,
                'latitude'   => $waypoint->latitude,
                'longitude'  => $waypoint->longitude,
                'scanned_at' => $firstScan->scanned_at,
                'media'      => $mediaItems,
                'index'      => ++$index,
            ];
        }

        $data = ['scenes' => $scenes];

        return view('traveler.cinematic-replay', compact('data', 'booking', 'token'));
    }
}