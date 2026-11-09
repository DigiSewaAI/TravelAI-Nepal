<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\ProviderType;
use App\Models\Provider;
use App\Models\RescueProvider;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'billing_interval' => 'nullable|in:monthly,yearly',
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

                // Rescue-specific validation (all optional)
                $selectedProviderType = ProviderType::find($request->provider_type);
                if ($selectedProviderType && $selectedProviderType->slug === 'rescue-service') {
                    $rules['aviation_license_number'] = 'nullable|string|max:100';
                    $rules['medical_cert_number'] = 'nullable|string|max:100';
                    $rules['insurance_policy_number'] = 'nullable|string|max:100';
                    $rules['max_altitude_ft'] = 'nullable|integer|min:0|max:30000';
                    $rules['coverage_radius_km'] = 'nullable|integer|min:10|max:500';
                    $rules['fleet_size'] = 'nullable|integer|min:1|max:100';
                    $rules['is_24_7'] = 'nullable|boolean';
                }
            }
        }

        $validated = $request->validate($rules);

        // Get selected plan (default: free)
        $planSlug = $request->plan ?? 'free';
        $plan = Plan::where('slug', $planSlug)->first();

        if (!$plan) {
            return back()->withErrors(['plan' => 'Selected plan not found.'])->withInput();
        }

        // FIX-01: Contact-only plans (Enterprise) must NOT create an active subscription
        if ($plan->isContactOnly()) {
            return redirect()
                ->route('public.contact-sales')
                ->with('info', __('messages.enterprise_requires_contact'));
        }

        // Determine billing interval
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

                    Log::info("New provider type created: {$customTypeName} (ID: {$providerType->id})");
                } else {
                    $providerType = ProviderType::find($request->provider_type);
                    if (!$providerType) {
                        throw new \Exception('Invalid provider type selected.');
                    }
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
                $provider->types()->attach($providerType->id);

                // Rescue-specific record (only if rescue-service type)
                if ($providerType->slug === 'rescue-service') {
                    RescueProvider::create([
                        'provider_id'                 => $provider->id,
                        'aviation_license_number'     => $request->filled('aviation_license_number') ? trim($request->aviation_license_number) : null,
                        'medical_cert_number'         => $request->filled('medical_cert_number') ? trim($request->medical_cert_number) : null,
                        'insurance_policy_number'     => $request->filled('insurance_policy_number') ? trim($request->insurance_policy_number) : null,
                        'coverage_radius_km'          => $request->filled('coverage_radius_km') ? (int) $request->coverage_radius_km : 50,
                        'max_altitude_ft'             => $request->filled('max_altitude_ft') ? (int) $request->max_altitude_ft : null,
                        'fleet_size'                  => $request->filled('fleet_size') ? (int) $request->fleet_size : 1,
                        'is_24_7'                     => $request->boolean('is_24_7'),
                        'is_available'                => true,
                    ]);

                    Log::info("Rescue provider created: {$provider->name} (ID: {$provider->id})");
                }

                // Create subscription with billing interval
                $isFree = $plan->isFree();

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
                    $subscriptionData['end_date'] = now()->addYear();
                } else {
                    $subscriptionData['status'] = 'pending';
                    $subscriptionData['start_date'] = now();
                    $subscriptionData['end_date'] = $billingInterval === 'yearly'
                        ? now()->addYear()
                        : now()->addMonth();
                }

                $subscription = Subscription::create($subscriptionData);

                DB::commit();

                // Cart Merge Fix for Provider
                $oldSessionId = session()->getId();
                Auth::login($user);
                app(\App\Services\CartService::class)->mergeGuestCart($user->id, $oldSessionId);

                // Notify admins: new provider registered
                try {
                    $__adminUrl = \Illuminate\Support\Facades\Route::has('admin.providers.show')
                        ? route('admin.providers.show', $provider->id)
                        : url('/admin/dashboard');
                    $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
                    foreach ($admins as $admin) {
                        $admin->notify(new \App\Notifications\AdminAlertNotification(
                            'New Provider Registered',
                            $user->name . ' (' . $user->email . ') - ' . ($provider->name ?? 'Business'),
                            'provider',
                            $__adminUrl,
                            $provider->id
                        ));
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Admin provider-reg notify failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                }

                // Redirect based on plan
                if ($isFree) {
                    return redirect()->route('provider.dashboard')
                        ->with('success', __('messages.registration_provider_success'));
                } else {
                    return redirect()->route('provider.payments.show', $subscription->id)
                        ->with('info', __('messages.registration_payment_required'));
                }
            }

            // Traveler registration
            DB::commit();

            // Cart Merge Fix for Traveler
            $oldSessionId = session()->getId();
            Auth::login($user);
            app(\App\Services\CartService::class)->mergeGuestCart($user->id, $oldSessionId);

            // Notify admins: new traveler registered
            try {
                $__adminUrl = \Illuminate\Support\Facades\Route::has('admin.users.show')
                    ? route('admin.users.show', $user->id)
                    : url('/admin/dashboard');
                $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\AdminAlertNotification(
                        'New Traveler Registered',
                        $user->name . ' (' . $user->email . ')',
                        'user',
                        $__adminUrl,
                        $user->id
                    ));
                }
            } catch (\Throwable $e) {
                \Log::warning('Admin traveler-reg notify failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }

            // Redirect intended checkout or dashboard
            return redirect()->intended(route(config('features.traveler_dashboard_v2', false) ? 'traveler.dashboard-v2' : 'traveler.dashboard'))
                ->with('success', __('messages.registration_traveler_success'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed: ' . $e->getMessage(), [
                'email' => $request->email,
                'provider_type' => $request->provider_type ?? null,
            ]);
            return back()->withErrors([
                'error' => __('messages.registration_failed'),
            ])->withInput();
        }
    }
}