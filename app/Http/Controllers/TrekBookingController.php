<?php

namespace App\Http\Controllers;

use App\Models\Trek;
use App\Models\Booking;
use App\Models\Trekker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TrekBookingController extends Controller
{
    public function create(Trek $trek)
    {
        return view('booking.create', compact('trek'));
    }

    public function store(Request $request, Trek $trek)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'start_date' => 'required|date|after_or_equal:today',
            'message' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // Create or find trekker
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
                'trekker_id'   => $trekker->id,
                'trek_id'      => $trek->id,
                'booking_date' => now(),
                'start_date'   => $validated['start_date'],
                'status'       => 'pending',
                'qr_code'      => $qrCode,
                'invoice_url'  => null,
            ]);

            DB::commit();

            return redirect()->route('booking.confirmation', $booking)
                ->with('success', 'Booking created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Something went wrong. Please try again.'])->withInput();
        }
    }

    public function confirmation(Booking $booking)
    {
        $booking->load('trek', 'trekker');
        return view('booking.confirmation', compact('booking'));
    }
}