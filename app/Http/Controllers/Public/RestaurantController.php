<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Models\RestaurantReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RestaurantController extends Controller
{
    public function show($slug)
    {
        // Load service with provider and category
        $service = Service::with(['provider', 'category'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        // Load Menu Items for this provider
        $menuItems = MenuItem::where('provider_id', $service->provider_id)
            ->where('is_available', true)
            ->orderBy('category')
            ->get();

        // Load Available Tables
        $tables = RestaurantTable::where('provider_id', $service->provider_id)
            ->orderBy('table_number')
            ->get();

        return view('public.restaurant.show', compact('service', 'menuItems', 'tables'));
    }

    public function reserve(Request $request, $slug)
    {
        $service = Service::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'table_id' => 'required|exists:restaurant_tables,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required',
            'guests' => 'required|integer|min:1|max:50',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $validated['provider_id'] = $service->provider_id;
        $validated['status'] = 'pending';

        $reservation = RestaurantReservation::create($validated);

        // 1. Send Email to Customer
        if ($reservation->customer_email) {
            try {
                \Mail::to($reservation->customer_email)->queue(new \App\Mail\ReservationCreatedMail($reservation));
            } catch (\Throwable $e) {
                \Log::warning('Reservation email failed', ['id' => $reservation->id, 'error' => $e->getMessage()]);
            }
        }

        // 2. Send Notification to Provider (Bell)
        try {
            $providerUser = $service->provider->user;
            if ($providerUser) {
                $providerUser->notify(new \App\Notifications\NewReservationNotification($reservation));
            }
        } catch (\Throwable $e) {
            \Log::warning('Reservation provider notify failed', ['id' => $reservation->id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Your table reservation request has been sent! The restaurant will confirm shortly.');
    }
}