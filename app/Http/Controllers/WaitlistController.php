<?php

namespace App\Http\Controllers;

use App\Models\Waitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException; // ✅ यो Line थप्नुहोस्

class WaitlistController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('Waitlist store called', ['email' => $request->email]);

            // ✅ Validation लाई पनि try भित्रै राख्यौं
            $validated = $request->validate([
                'email' => 'required|email|unique:waitlist,email',
            ]);

            $waitlist = Waitlist::create($validated);
            Log::info('Waitlist saved successfully', ['id' => $waitlist->id]);

            // ✅ Send confirmation email
            try {
                Mail::to($validated['email'])->send(new \App\Mail\WaitlistConfirmation($validated['email']));
                Log::info('Waitlist confirmation email sent to: ' . $validated['email']);
            } catch (\Exception $e) {
                Log::error('Failed to send waitlist confirmation email: ' . $e->getMessage());
                // Email नपठाए पनि request सफल मान्ने
            }

            return response()->json([
                'success' => true,
                'message' => 'Thanks! You\'re on the waitlist. We\'ll notify you at launch.',
            ]);

        // ✅ अब Validation Exception लाई पनि छुट्टै समात्छौं
        } catch (ValidationException $e) {
            Log::warning('Waitlist validation failed', [
                'errors' => $e->errors(),
                'email' => $request->email
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Waitlist error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }
}