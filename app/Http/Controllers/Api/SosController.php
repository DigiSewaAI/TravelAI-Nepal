<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\SosAlert;
use App\Models\Trekker;
use App\Jobs\SendSosNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SosController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id'    => 'required|exists:bookings,id',
            'trekker_id'    => 'required|exists:trekkers,id',
            'latitude'      => 'required|numeric',
            'longitude'     => 'required|numeric',
            'message'       => 'nullable|string|max:500',
        ]);

        $sos = SosAlert::create([
            'trekker_id'   => $validated['trekker_id'],
            'booking_id'   => $validated['booking_id'],
            'latitude'     => $validated['latitude'],
            'longitude'    => $validated['longitude'],
            'message'      => $validated['message'] ?? null,
            'is_resolved'  => false,
        ]);

        // Dispatch queue job for email/notification
        SendSosNotification::dispatch($sos);

        return response()->json([
            'success' => true,
            'message' => 'SOS alert received. Agencies have been notified.',
            'sos_id'  => $sos->id
        ], 201);
    }
}