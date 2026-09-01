<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Models\TravelerSafetyAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function markAsRead(Request $request, $alertId)
    {
        $alert = TravelerSafetyAlert::where('id', $alertId)
            ->where('user_id', Auth::id())
            ->first();

        if ($alert) {
            $alert->read_at = now();
            $alert->save();
            return redirect()->back()->with('success', 'Alert marked as read');
        }

        return redirect()->back()->with('error', 'Alert not found');
    }
}