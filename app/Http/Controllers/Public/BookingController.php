<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Booking;
use App\Models\Trekker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function create($serviceSlug)
    {
        $service = Service::where('slug', $serviceSlug)
            ->where('status', 'active')
            ->firstOrFail();

        return view('public.booking.create', compact('service'));
    }

    public function store(Request $request, $serviceSlug)
    {
        $service = Service::where('slug', $serviceSlug)
            ->where('status', 'active')
            ->firstOrFail();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'start_date' => 'required|date|after_or_equal:today',
            'message' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Check if user exists, if not create one (traveler)
            $user = User::where('email', $validated['email'])->first();
            if (!$user) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => bcrypt(Str::random(32)),
                    'role' => 'traveler',
                    'phone' => $validated['phone'],
                ]);
            }

            // Also keep legacy trekker for backward compatibility
            $trekker = Trekker::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                ]
            );

            // Generate unique QR code
            $qrCode = Str::random(32);

            // Create booking
            $booking = Booking::create([
                'traveler_id'  => $user->id,
                'service_id'   => $service->id,
                'trekker_id'   => $trekker->id, // legacy
                'booking_date' => now(),
                'start_date'   => $validated['start_date'],
                'status'       => 'pending',
                'qr_code'      => $qrCode,
                'invoice_url'  => null,
            ]);

            DB::commit();

            return redirect()->route('public.booking.confirmation', $booking)
                ->with('success', 'Booking created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong. Please try again.'])->withInput();
        }
    }

    public function confirmation(Booking $booking)
    {
        $booking->load(['service.provider', 'traveler']);
        return view('public.booking.confirmation', compact('booking'));
    }
}