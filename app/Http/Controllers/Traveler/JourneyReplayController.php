<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Services\JourneyReplay\JourneyReplayService;
use App\Models\Booking;  // ✅ यो use थप्नुहोस्
use Illuminate\Support\Facades\Auth;

class JourneyReplayController extends Controller
{
    protected $replayService;

    public function __construct(JourneyReplayService $replayService)
    {
        $this->replayService = $replayService;
    }

    public function index()
    {
        $user = Auth::user();
        $replayData = $this->replayService->getReplay($user);

        // 🔥 FIXED: $user->bookings() को सट्टा Booking::where() प्रयोग गरौं
        $booking = null;

        if (isset($replayData['booking']) && $replayData['booking'] instanceof Booking) {
            $booking = $replayData['booking'];
        } else {
            // ✅ सिधा Booking मोडेलबाट क्वेरी गरौं
            $booking = Booking::where('traveler_id', $user->id)
                              ->latest('start_date')  // or latest('created_at')
                              ->first();
        }

        return view('traveler.journey-replay', compact('replayData', 'booking'));
    }
}