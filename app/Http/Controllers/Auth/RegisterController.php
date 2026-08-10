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
        ];

        // If provider_type is provided (checkbox checked), add extra validation
        if ($request->filled('provider_type')) {
            $rules['business_name'] = 'required|string|max:255';
            $rules['provider_type'] = 'required|exists:provider_types,id';
        }

        $request->validate($rules);

        // Get the selected plan (default to 'free')
        $planSlug = $request->plan ?? 'free';
        $plan = Plan::where('slug', $planSlug)->first();

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->filled('provider_type') ? 'provider_owner' : 'traveler',
            ]);

            // If provider registration
            if ($request->filled('provider_type')) {
                $providerType = ProviderType::find($request->provider_type);

                // Create provider
                $provider = Provider::create([
                    'user_id' => $user->id,
                    'name' => $request->business_name,
                    'slug' => Str::slug($request->business_name) . '-' . Str::random(6),
                    'contact_email' => $request->email,
                    'contact_phone' => $request->phone ?? null,
                    'address' => $request->address ?? null,
                    'verification_status' => 'pending',
                    'is_active' => true,
                ]);

                // Attach provider type
                $provider->types()->attach($providerType->id);

                // Create subscription
                Subscription::create([
                    'provider_id' => $provider->id,
                    'plan_id' => $plan->id,
                    'start_date' => now(),
                    'end_date' => null,
                    'status' => 'active',
                ]);

                DB::commit();

                Auth::login($user);
                return redirect()->route('provider.dashboard')
                    ->with('success', 'Provider account created successfully!');
            }

            // Traveler registration
            DB::commit();
            Auth::login($user);
            return redirect()->route('home')
                ->with('success', 'Account created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    }
}