<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Display subscription management page.
     */
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

    /**
     * Store a new subscription (plan selection).
     * For paid plans, redirects to payment page.
     * For free plans, activates immediately.
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_interval' => 'sometimes|in:monthly,yearly', // ✅ New
        ]);

        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $plan = Plan::findOrFail($request->plan_id);
        $billingInterval = $request->input('billing_interval', 'monthly');

        // Check if already has an active subscription
        $existing = $provider->subscriptions()
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return back()->with('error', 'You already have an active subscription. Please cancel it first.');
        }

        // Create subscription with pending status
        $subscription = Subscription::create([
            'provider_id' => $provider->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'billing_interval' => $billingInterval, // ✅ Store interval
            'start_date' => null,
            'end_date' => null,
        ]);

        // If plan is free, activate immediately
        $isFree = ($plan->price_monthly ?? 0) == 0 && ($plan->price_yearly ?? 0) == 0;

        if ($isFree) {
            $subscription->status = 'active';
            $subscription->start_date = now();
            $subscription->end_date = now()->addYear(); // Free plans usually yearly
            $subscription->save();

            return redirect()->route('provider.subscriptions.index')
                ->with('success', 'Free plan activated successfully!');
        }

        // Redirect to payment page for paid plans
        return redirect()->route('provider.payments.show', $subscription->id)
            ->with('info', 'Please complete the payment to activate your subscription.');
    }

    /**
     * Upgrade to a different plan.
     * Cancels current subscription and creates a new one.
     * For paid plans, redirects to payment.
     */
    public function upgrade(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_interval' => 'sometimes|in:monthly,yearly', // ✅ New
        ]);

        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $plan = Plan::findOrFail($request->plan_id);
        $billingInterval = $request->input('billing_interval', 'monthly');

        // Check if already on this plan
        $current = $provider->subscriptions()
            ->where('status', 'active')
            ->with('plan')
            ->first();

        if ($current && $current->plan_id == $plan->id) {
            return back()->with('error', 'You are already on the ' . $plan->name . ' plan.');
        }

        // Cancel current active subscription
        if ($current) {
            $current->status = 'cancelled';
            $current->end_date = now();
            $current->save();
        }

        // Create new subscription with pending status
        $subscription = Subscription::create([
            'provider_id' => $provider->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'billing_interval' => $billingInterval, // ✅ Store interval
            'start_date' => null,
            'end_date' => null,
        ]);

        // If plan is free, activate immediately
        $isFree = ($plan->price_monthly ?? 0) == 0 && ($plan->price_yearly ?? 0) == 0;

        if ($isFree) {
            $subscription->status = 'active';
            $subscription->start_date = now();
            $subscription->end_date = now()->addYear();
            $subscription->save();

            return redirect()->route('provider.subscriptions.index')
                ->with('success', 'Plan upgraded to ' . $plan->name . ' (Free) successfully!');
        }

        // Redirect to payment page for paid plans
        return redirect()->route('provider.payments.show', $subscription->id)
            ->with('info', 'Please complete the payment to activate your new plan.');
    }

    /**
     * Cancel the current active subscription.
     */
    public function cancel()
    {
        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $subscription = $provider->subscriptions()
            ->where('status', 'active')
            ->first();

        if (!$subscription) {
            return back()->with('error', 'No active subscription to cancel.');
        }

        $subscription->status = 'cancelled';
        $subscription->end_date = now();
        $subscription->save();

        return back()->with('success', 'Subscription cancelled successfully.');
    }

    /**
     * Resume a cancelled subscription (reactivate).
     */
    public function resume(Request $request)
    {
        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $subscription = $provider->subscriptions()
            ->where('status', 'cancelled')
            ->latest()
            ->first();

        if (!$subscription) {
            return back()->with('error', 'No cancelled subscription found.');
        }

        // If the plan is free, reactivate immediately
        $plan = $subscription->plan;
        $isFree = ($plan->price_monthly ?? 0) == 0 && ($plan->price_yearly ?? 0) == 0;

        if ($isFree) {
            $subscription->status = 'active';
            $subscription->start_date = now();
            $subscription->end_date = now()->addYear();
            $subscription->save();

            return back()->with('success', 'Subscription resumed successfully!');
        }

        // For paid plans, create a new subscription and redirect to payment
        // Use existing billing_interval or default to monthly
        $newSubscription = Subscription::create([
            'provider_id' => $provider->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'billing_interval' => $subscription->billing_interval ?? 'monthly',
            'start_date' => null,
            'end_date' => null,
        ]);

        return redirect()->route('provider.payments.show', $newSubscription->id)
            ->with('info', 'Please complete the payment to resume your subscription.');
    }
}