<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     *
     * 🧪 LOCAL: Activates immediately (payment bypassed)
     * 🔒 PRODUCTION: Creates pending subscription & redirects to payment
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_interval' => 'sometimes|in:monthly,yearly',
        ]);

        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $plan = Plan::findOrFail($request->plan_id);
        $billingInterval = $request->input('billing_interval', 'monthly');

        // FIX-01: Contact-only plans (Enterprise) must NOT create an active subscription
        if ($plan->isContactOnly()) {
            return redirect()
                ->route('public.contact-sales')
                ->with('info', __('messages.enterprise_requires_contact'));
        }

        // Check if already has an active subscription
        $existing = $provider->subscriptions()
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return back()->with('error', 'You already have an active subscription. Please cancel it first.');
        }

        // 🌍 Environment check
        $isLocal = app()->environment('local');

        if ($isLocal) {
            // 🧪 LOCAL: Activate immediately without payment
            $subscription = Subscription::create([
                'provider_id' => $provider->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_interval' => $billingInterval,
                'start_date' => now(),
                'end_date' => $billingInterval === 'yearly' ? now()->addYear() : now()->addMonth(),
            ]);

            return redirect()->route('provider.subscriptions.index')
                ->with('success', $plan->name . ' plan activated successfully!');
        } else {
            // 🔒 PRODUCTION: Create pending, redirect to payment
            $subscription = Subscription::create([
                'provider_id' => $provider->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'billing_interval' => $billingInterval,
                'start_date' => now(),  // PHASE 7C-FIX: NOT NULL constraint — set tentative start
                'end_date' => $billingInterval === 'yearly' ? now()->addYear() : now()->addMonth(),
            ]);

            // For free plans, activate immediately even in production
            $isFree = $plan->isFree();

            if ($isFree) {
                $subscription->status = 'active';
                $subscription->start_date = now();
                $subscription->end_date = now()->addYear();
                $subscription->save();

                return redirect()->route('provider.subscriptions.index')
                    ->with('success', $plan->name . ' plan activated successfully!');
            }

            return redirect()->route('provider.payments.show', $subscription->id)
                ->with('info', 'Please complete the payment to activate your subscription.');
        }
    }

    /**
     * Upgrade to a different plan.
     * Cancels current subscription and creates a new one.
     *
     * 🧪 LOCAL: Activates immediately (payment bypassed)
     * 🔒 PRODUCTION: Creates pending subscription & redirects to payment
     */
    public function upgrade(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_interval' => 'sometimes|in:monthly,yearly',
        ]);

        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $plan = Plan::findOrFail($request->plan_id);
        $billingInterval = $request->input('billing_interval', 'monthly');

        // FIX-01: Contact-only plans (Enterprise) must NOT create an active subscription
        if ($plan->isContactOnly()) {
            return redirect()
                ->route('public.contact-sales')
                ->with('info', __('messages.enterprise_requires_contact'));
        }

        // Check if already on this plan
        $current = $provider->subscriptions()
            ->where('status', 'active')
            ->with('plan')
            ->first();

        if ($current && $current->plan_id == $plan->id) {
            return back()->with('error', 'You are already on the ' . $plan->name . ' plan.');
        }

        // RACE FIX (2026-09-29): Cleanup old pending subs to prevent duplicates
        $provider->subscriptions()
            ->where('status', 'pending')
            ->update(['status' => 'cancelled', 'end_date' => now()]);

        $isLocal = app()->environment('local');

        if ($isLocal) {
            // LOCAL: Cancel current + activate new (dev convenience)
            if ($current) {
                $current->status = 'cancelled';
                $current->end_date = now();
                $current->save();
            }

            Subscription::create([
                'provider_id' => $provider->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_interval' => $billingInterval,
                'start_date' => now(),
                'end_date' => $billingInterval === 'yearly' ? now()->addYear() : now()->addMonth(),
            ]);

            return redirect()->route('provider.subscriptions.index')
                ->with('success', 'Plan upgraded to ' . $plan->name . ' successfully!');
        }

        // PRODUCTION
        $isFree = $plan->isFree();

        if ($isFree) {
            // FREE plan: Cancel current + activate new (atomic transaction)
            DB::transaction(function () use ($current, $provider, $plan, $billingInterval) {
                if ($current) {
                    $current->status = 'cancelled';
                    $current->end_date = now();
                    $current->save();
                }

                Subscription::create([
                    'provider_id' => $provider->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'billing_interval' => $billingInterval,
                    'start_date' => now(),
                    'end_date' => now()->addYear(),
                ]);
            });

            return redirect()->route('provider.subscriptions.index')
                ->with('success', 'Plan upgraded to ' . $plan->name . ' (Free) successfully!');
        }

        // PAID plan: Do NOT cancel current — approve() will handle after payment verified
        // RACE FIX: Provider keeps current plan if payment fails/abandoned
        $subscription = Subscription::create([
            'provider_id' => $provider->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'billing_interval' => $billingInterval,
            'start_date' => now(),
            'end_date' => $billingInterval === 'yearly' ? now()->addYear() : now()->addMonth(),
        ]);

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
     *
     * 🧪 LOCAL: Activates immediately (payment bypassed)
     * 🔒 PRODUCTION: Creates pending subscription & redirects to payment
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

        $plan = $subscription->plan;
        $billingInterval = $subscription->billing_interval ?? 'monthly';

        // FIX-01: Contact-only plans (Enterprise) must NOT create an active subscription
        if ($plan->isContactOnly()) {
            return redirect()
                ->route('public.contact-sales')
                ->with('info', __('messages.enterprise_requires_contact'));
        }

        // 🌍 Environment check
        $isLocal = app()->environment('local');

        if ($isLocal) {
            // 🧪 LOCAL: Activate immediately without payment
            $newSubscription = Subscription::create([
                'provider_id' => $provider->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_interval' => $billingInterval,
                'start_date' => now(),
                'end_date' => $billingInterval === 'yearly' ? now()->addYear() : now()->addMonth(),
            ]);

            return back()->with('success', 'Subscription resumed successfully!');
        } else {
            // 🔒 PRODUCTION: Check if free plan
            $isFree = $plan->isFree();

            // If free, reactivate immediately
            if ($isFree) {
                $subscription->status = 'active';
                $subscription->start_date = now();
                $subscription->end_date = now()->addYear();
                $subscription->save();

                return back()->with('success', 'Subscription resumed successfully!');
            }

            // 🔒 Production: Create pending, redirect to payment
            $newSubscription = Subscription::create([
                'provider_id' => $provider->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'billing_interval' => $billingInterval,
                'start_date' => now(),  // PHASE 7C-FIX: NOT NULL constraint
                'end_date' => $billingInterval === 'yearly' ? now()->addYear() : now()->addMonth(),
            ]);

            return redirect()->route('provider.payments.show', $newSubscription->id)
                ->with('info', 'Please complete the payment to resume your subscription.');
        }
    }
}