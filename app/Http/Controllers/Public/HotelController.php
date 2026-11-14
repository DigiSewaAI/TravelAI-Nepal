<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\HotelRoom;
use App\Models\ProviderOffer;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function show($slug)
    {
        $service = Service::with(['provider', 'category', 'hotelDetail'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $provider = $service->provider;
        $hotelDetail = $service->hotelDetail;

        $rooms = HotelRoom::where('provider_id', $service->provider_id)
            ->orderBy('room_number')
            ->get();

        $offers = ProviderOffer::where('provider_id', $service->provider_id)
            ->where('is_active', true)
            ->where('valid_until', '>=', now())
            ->get();

        return view('public.hotel.show', compact('service', 'provider', 'hotelDetail', 'rooms', 'offers'));
    }

    public function book(Request $request, $slug)
    {
        // Hotel Booking Logic (Future implementation)
        return back()->with('success', 'Booking request received. We will contact you shortly.');
    }
}