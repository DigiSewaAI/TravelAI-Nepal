<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $currentSubscription = $provider->subscriptions()
            ->where('status', 'active')
            ->with('plan')
            ->first();

        $plans = Plan::all();

        return view('provider.subscriptions.index', compact('currentSubscription', 'plans'));
    }

    public function upgrade(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $plan = Plan::findOrFail($request->plan_id);

        // Cancel current active subscription
        $current = $provider->subscriptions()->where('status', 'active')->first();
        if ($current) {
            $current->status = 'cancelled';
            $current->end_date = now();
            $current->save();
        }

        // Create new subscription
        Subscription::create([
            'provider_id' => $provider->id,
            'plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => null,
            'status' => 'active',
        ]);

        return back()->with('success', 'Plan upgraded to ' . $plan->name . ' successfully.');
    }

    public function cancel()
    {
        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $subscription = $provider->subscriptions()->where('status', 'active')->first();
        if ($subscription) {
            $subscription->status = 'cancelled';
            $subscription->end_date = now();
            $subscription->save();
        }

        return back()->with('success', 'Subscription cancelled successfully.');
    }
}