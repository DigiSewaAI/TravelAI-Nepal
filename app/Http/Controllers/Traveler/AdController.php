<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Services\AdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    public function __construct(private AdService $adService) {}

    /**
     * Track impression (AJAX).
     */
    public function impression(Request $request, Ad $ad)
    {
        $this->adService->trackImpression(
            $ad,
            Auth::id(),
            $request->ip()
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Track click + redirect.
     */
    public function click(Request $request, Ad $ad)
    {
        $this->adService->trackClick(
            $ad,
            Auth::id(),
            $request->ip()
        );

        return redirect()->away($ad->link_url);
    }
}