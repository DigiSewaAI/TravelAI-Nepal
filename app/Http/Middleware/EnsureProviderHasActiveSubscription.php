<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderHasActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. यदि युजर provider_owner होइनन् भने (जस्तै: traveler वा admin) सिधै पास गर्ने
        if (!$user || $user->role !== 'provider_owner') {
            return $next($request);
        }

        $provider = $user->provider; // User model मा provider relation छ

        // 2. यदि प्रोभाइडर प्रोफाइल नै छैन भने (register flow मा हुन सक्छ) पास गर्ने
        if (!$provider) {
            return $next($request);
        }

        // 3. Payment र Subscription रुटहरूलाई रोक्न हुँदैन (नत्र पेमेन्ट पेज नै खुल्दैन)
        if ($request->routeIs('provider.payments.*') || $request->routeIs('provider.subscriptions.*')) {
            return $next($request);
        }

        // 4. सब्स्क्रिप्शन स्टेटस चेक गर्ने
        $subscription = $provider->subscriptions()->latest()->first();

        if (!$subscription || $subscription->status !== 'active') {
            if ($subscription) {
                // यदि pending छ भने पेमेन्ट पेजमा पठाउने
                return redirect()->route('provider.payments.show', $subscription->id)
                    ->with('warning', 'Your subscription is pending. Please complete the payment to access your dashboard.');
            }
            
            // यदि कुनै सब्स्क्रिप्शन नै छैन भने सब्स्क्रिप्शन पेजमा पठाउने
            return redirect()->route('provider.subscriptions.index')
                ->with('warning', 'You do not have an active subscription. Please choose a plan.');
        }

        return $next($request);
    }
}