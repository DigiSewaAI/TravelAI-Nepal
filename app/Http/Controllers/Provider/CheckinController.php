<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\QrScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckinController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $provider = $user->providers()->first();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $serviceIds = $provider->services()->pluck('id');

        $query = QrScan::whereIn('booking_id', function ($q) use ($serviceIds) {
            $q->select('id')->from('bookings')->whereIn('service_id', $serviceIds);
        })->with(['booking.traveler', 'booking.service']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('booking.traveler', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('booking.service', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('scanned_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scanned_at', '<=', $request->date_to);
        }

        $checkins = $query->latest('scanned_at')->paginate(20);
        $services = $provider->services;

        return view('provider.checkins.index', compact('checkins', 'services', 'provider'));
    }

    public function show(QrScan $scan)
    {
        $provider = Auth::user()->providers()->first();
        $serviceIds = $provider->services()->pluck('id');

        $booking = $scan->booking;
        if (!$booking || !in_array($booking->service_id, $serviceIds->toArray())) {
            abort(403);
        }

        // ✅ Correct eager loading
        $scan->load(['booking.traveler', 'booking.service', 'booking.service.provider']);

        return view('provider.checkins.show', compact('scan'));
    }
}