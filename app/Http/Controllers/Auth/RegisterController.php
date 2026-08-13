<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\ProviderType;
use App\Models\Provider;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Show the registration form with plans and provider types.
     */
    public function showRegistrationForm(Request $request)
    {
        $plans = Plan::all();
        $providerTypes = ProviderType::all();
        return view('auth.register', compact('plans', 'providerTypes'));
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
{
    // Base validation rules
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:6|confirmed',
        'plan' => 'nullable|exists:plans,slug',
        'phone' => 'nullable|string|max:20',
        'billing_interval' => 'nullable|in:monthly,yearly', // ✅ New
    ];

    // If provider registration (provider_type field exists)
    if ($request->filled('provider_type')) {
        $rules['business_name'] = 'required|string|max:255';
        $rules['address'] = 'nullable|string|max:500';
        
        // If "Other" selected, custom_provider_type is required
        if ($request->provider_type == 'other') {
            $rules['custom_provider_type'] = 'required|string|max:255';
        } else {
            $rules['provider_type'] = 'required|exists:provider_types,id';
        }
    }

    $validated = $request->validate($rules);

    // Get selected plan (default: free)
    $planSlug = $request->plan ?? 'free';
    $plan = Plan::where('slug', $planSlug)->first();

    if (!$plan) {
        return back()->withErrors(['plan' => 'Selected plan not found.'])->withInput();
    }

    // ✅ Determine billing interval
    $billingInterval = $request->input('billing_interval', 'monthly');

    DB::beginTransaction();

    try {
        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->filled('provider_type') ? 'provider_owner' : 'traveler',
            'phone' => $request->phone,
        ]);

        // If provider registration
        if ($request->filled('provider_type')) {
            // Determine provider type ID
            if ($request->provider_type == 'other') {
                $customTypeName = trim($request->custom_provider_type);
                $customTypeSlug = Str::slug($customTypeName);
                
                $providerType = ProviderType::firstOrCreate(
                    ['slug' => $customTypeSlug],
                    ['name' => $customTypeName]
                );
                
                $providerTypeId = $providerType->id;
                \Log::info("New provider type created: {$customTypeName} (ID: {$providerTypeId})");
            } else {
                $providerType = ProviderType::find($request->provider_type);
                if (!$providerType) {
                    throw new \Exception('Invalid provider type selected.');
                }
                $providerTypeId = $providerType->id;
            }

            // Create provider
            $provider = Provider::create([
                'user_id' => $user->id,
                'name' => $request->business_name,
                'slug' => Str::slug($request->business_name) . '-' . Str::random(6),
                'description' => $request->description ?? null,
                'contact_email' => $request->email,
                'contact_phone' => $request->phone ?? null,
                'address' => $request->address ?? null,
                'verification_status' => 'pending',
                'is_active' => true,
            ]);

            // Attach provider type
            $provider->types()->attach($providerTypeId);

            // ✅ Create subscription with billing interval
            $isFree = ($plan->price_monthly ?? 0) == 0 && ($plan->price_yearly ?? 0) == 0;

            $subscriptionData = [
                'provider_id' => $provider->id,
                'plan_id' => $plan->id,
                'billing_interval' => $billingInterval,
                'start_date' => null,
                'end_date' => null,
            ];

            if ($isFree) {
                $subscriptionData['status'] = 'active';
                $subscriptionData['start_date'] = now();
                $subscriptionData['end_date'] = now()->addYear(); // Free plan: 1 year
            } else {
                $subscriptionData['status'] = 'pending'; // Payment required
            }

            $subscription = Subscription::create($subscriptionData);

            DB::commit();

            Auth::login($user);

            // Redirect based on plan
            if ($isFree) {
                return redirect()->route('provider.dashboard')
                    ->with('success', '🎉 Free plan activated! Welcome to TravelAI Nepal.');
            } else {
                // Redirect to payment page
                return redirect()->route('provider.payments.show', $subscription->id)
                    ->with('info', 'Please complete the payment to activate your subscription.');
            }
        }

        // Traveler registration
        DB::commit();
        Auth::login($user);
        return redirect()->route('home')
            ->with('success', '🎉 Account created successfully! Welcome to TravelAI Nepal.');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Registration failed: ' . $e->getMessage(), [
            'email' => $request->email,
            'provider_type' => $request->provider_type ?? null,
        ]);
        return back()->withErrors([
            'error' => 'Registration failed: ' . $e->getMessage()
        ])->withInput();
    }
}
}