<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuotationRequest;
use App\Models\PlannerResult;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class QuotationRequestController extends Controller
{
    /**
     * Get list of active providers for dropdown.
     */
    public function providersList()
    {
        try {
            $providers = Provider::where('is_active', true)
                ->select('id', 'name', 'verification_status')
                ->orderBy('name')
                ->get();

            return response()->json(['providers' => $providers]);
        } catch (\Exception $e) {
            Log::error('Providers list error: ' . $e->getMessage());
            return response()->json(['providers' => [], 'error' => 'Unable to load providers'], 500);
        }
    }

    /**
     * Store a new quotation request from a traveler.
     */
    public function store(Request $request)
    {
        Log::info('🔍 [QuotationRequest] store STARTED', [
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'payload' => $request->all()
        ]);

        try {
            $validated = $request->validate([
    'planner_result_id' => 'required|exists:planner_results,id',
    'provider_id' => 'required|exists:providers,id',
    'traveler_name' => 'required|string|max:255',
    'traveler_email' => 'required|email|max:255',
    'traveler_phone' => 'nullable|string|max:20',
    'message' => 'nullable|string|max:500',
]);

            Log::info('✅ [QuotationRequest] Validation passed', ['validated' => $validated]);

            $user = Auth::user();

            // 2. Get planner result with plannerRequest
            $plannerResult = PlannerResult::with('plannerRequest')->find($validated['planner_result_id']);
            
            if (!$plannerResult || !$plannerResult->plannerRequest) {
                Log::warning('⚠️ [QuotationRequest] Itinerary not found', ['id' => $validated['planner_result_id']]);
                return response()->json([
                    'success' => false,
                    'message' => 'Itinerary not found.'
                ], 404);
            }

            $plannerRequest = $plannerResult->plannerRequest;

            // ✅ AUTHORIZATION: Check user ownership (session check removed)
            if ($user) {
                // Registered user: must own the itinerary
                if ($plannerRequest->user_id !== $user->id) {
                    Log::warning('⚠️ [QuotationRequest] Unauthorized: user mismatch', [
                        'user' => $user->id,
                        'owner' => $plannerRequest->user_id
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not own this itinerary.'
                    ], 403);
                }
            } else {
                // Guest: allow only if plannerRequest has no user_id (guest itinerary)
                if ($plannerRequest->user_id !== null) {
                    Log::warning('⚠️ [QuotationRequest] Guest trying to access owned itinerary', [
                        'owner' => $plannerRequest->user_id
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized.'
                    ], 403);
                }
                // If plannerRequest->user_id is null, it's a guest itinerary → allow
            }

            // 4. Prevent duplicate pending requests
            $existing = QuotationRequest::where([
                'planner_result_id' => $validated['planner_result_id'],
                'provider_id' => $validated['provider_id'],
            ])->whereIn('status', ['pending', 'viewed', 'processing'])->first();

            if ($existing) {
                Log::info('ℹ️ [QuotationRequest] Duplicate request blocked', ['existing_id' => $existing->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'You have already sent a quotation request to this provider for this itinerary.'
                ], 409);
            }

            // 5. Build itinerary snapshot
            $itineraryData = [
                'days' => $plannerResult->days()->with('items')->get()->toArray(),
                'total_cost' => $plannerRequest->total_cost ?? 0,
                'breakdown' => $plannerRequest->breakdown ?? [],
            ];

            // 6. Create request
            $quotationRequest = QuotationRequest::create([
                'traveler_id' => $user ? $user->id : null,
                'traveler_name' => $validated['traveler_name'],
                'traveler_email' => $validated['traveler_email'],
                'traveler_phone' => $validated['traveler_phone'] ?? null,
                'provider_id' => $validated['provider_id'],
                'planner_result_id' => $validated['planner_result_id'],
                'itinerary_data' => $itineraryData,
                'traveler_input' => $plannerRequest->only(['destination', 'days', 'budget', 'travel_style', 'interests']),
                'message' => $validated['message'] ?? null,
                'status' => 'pending',
            ]);

            Log::info('✅ [QuotationRequest] Request created', ['request_id' => $quotationRequest->id]);

            // 7. Notify provider
            $providerUser = $quotationRequest->provider->user;
            if ($providerUser) {
                try {
                    $providerUser->notify(new \App\Notifications\QuotationRequestedNotification($quotationRequest));
                    Log::info('📧 [QuotationRequest] Notification sent to provider', ['provider_user_id' => $providerUser->id]);
                } catch (\Exception $e) {
                    Log::error('❌ [QuotationRequest] Notification failed: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Quotation request sent successfully! The provider will review it shortly.',
                'request_id' => $quotationRequest->id,
            ]);

        } catch (ValidationException $e) {
            Log::warning('⚠️ [QuotationRequest] Validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('🚨 [QuotationRequest] Unexpected error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}