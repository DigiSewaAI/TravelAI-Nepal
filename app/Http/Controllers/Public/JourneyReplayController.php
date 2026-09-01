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

        return view('public.cinematic-replay', compact('booking', 'token'));
    }
}