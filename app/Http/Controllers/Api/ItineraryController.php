<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AiQuotaExceededException;
use App\Http\Controllers\Controller;
use App\Services\AiReservationService;
use App\Services\ItineraryGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ItineraryController extends Controller
{
    /**
     * FIX-12: Guest itinerary generation with quota reservation.
     *
     * - Identity: normalized IP hash (per Master D2)
     * - Reservation: short DB transaction, then LLM call, then finalize/release
     * - Quota: 5/day per guest IP hash
     */
    public function generate(
        Request $request,
        ItineraryGenerator $generator,
        AiReservationService $reservations
    ) {
        $request->validate([
            'destination' => 'required|string|max:255',
            'days' => 'required|integer|min:1|max:30',
            'budget' => 'required|integer|min:100',
            'travel_style' => 'required|string|in:budget,mid-range,luxury,backpacker',
            'interests' => 'nullable|string|max:500',
        ]);

        $payload = $request->only(['destination', 'days', 'budget', 'travel_style', 'interests']);
        $ipHash = AiReservationService::normalizeIpHash($request->ip());

        $reservation = null;

        try {
            // FIX-12: crash-safe guest reservation (short transaction)
            $key = AiReservationService::generateIdempotencyKey(
                'guest:' . $ipHash,
                'api.itinerary.generate',
                $payload
            );

            $reservation = $reservations->reserveForGuest(
                $ipHash,
                'api.itinerary.generate',
                $key
            );

            // FIX-12: LLM call OUTSIDE any DB transaction
            $itinerary = $generator->generate($payload);

            // Detect error-string contract (preserves existing behavior)
            if (str_starts_with($itinerary, ItineraryGenerator::ERROR_PREFIX)) {
                // LLM failed -> release quota, keep original response shape
                $reservations->release($reservation);
            } else {
                // LLM succeeded -> finalize
                $reservations->finalize($reservation);
            }

            return response()->json([
                'success'   => true,
                'itinerary' => nl2br(e($itinerary)),
            ]);

        } catch (AiQuotaExceededException $e) {
            Log::info('Guest AI quota exceeded', [
                'ip_hash'  => $ipHash,
                'endpoint' => 'api.itinerary.generate',
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 429);

        } catch (\Exception $e) {
            // FIX-12: release reservation on failure
            if ($reservation) {
                try {
                    $reservations->release($reservation);
                } catch (\Throwable $releaseError) {
                    Log::error('Failed to release AI reservation', [
                        'reservation_id' => $reservation->id,
                        'error'          => $releaseError->getMessage(),
                    ]);
                }
            }

            Log::error('AI Itinerary Generation Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}