<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ProviderStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * List all staff members for the current provider
     */
    public function index()
    {
        $provider = Auth::user()->provider;

        $staff = ProviderStaff::where('provider_id', $provider->id)
                    ->with('user')
                    ->get();

        $maxStaff = $this->getMaxStaff($provider);

        return view('provider.staff.index', compact('staff', 'maxStaff'));
    }

    /**
     * Show the form to add a new staff member
     */
    public function create()
    {
        $provider = Auth::user()->provider;
        $maxStaff = $this->getMaxStaff($provider);
        $currentStaffCount = ProviderStaff::where('provider_id', $provider->id)->count();

        if ($currentStaffCount >= $maxStaff && $maxStaff != -1) {
            // ✅ No HTML in error message – plain text only
            return redirect()->route('provider.staff.index')
                ->with('error', 'You have reached your staff limit. Please upgrade your plan to add more staff.');
        }

        return view('provider.staff.create');
    }

    /**
     * Store a new staff member
     */
    public function store(Request $request)
    {
        $provider = Auth::user()->provider;
        $maxStaff = $this->getMaxStaff($provider);
        $currentStaffCount = ProviderStaff::where('provider_id', $provider->id)->count();

        if ($currentStaffCount >= $maxStaff && $maxStaff != -1) {
            return redirect()->route('provider.staff.index')
                ->with('error', 'Staff limit reached. Please upgrade your plan.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|max:255',
        ]);

        // 1. Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'staff',
        ]);

        // 2. Create ProviderStaff entry
        ProviderStaff::create([
            'user_id' => $user->id,
            'provider_id' => $provider->id,
            'role' => $validated['role'] ?? 'staff',
            'permissions' => [],
        ]);

        return redirect()->route('provider.staff.index')
            ->with('success', 'Staff member added successfully.');
    }

    /**
     * Show the edit form for a staff member
     */
    public function edit(ProviderStaff $staff)
    {
        if ($staff->provider_id !== Auth::user()->provider->id) {
            abort(403, 'Unauthorized.');
        }
        return view('provider.staff.edit', compact('staff'));
    }

    /**
     * Update a staff member's details
     */
    public function update(Request $request, ProviderStaff $staff)
    {
        if ($staff->provider_id !== Auth::user()->provider->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
        ]);

        $staff->user->update(['name' => $validated['name']]);
        $staff->update(['role' => $validated['role'] ?? $staff->role]);

        return redirect()->route('provider.staff.index')
            ->with('success', 'Staff updated successfully.');
    }

    /**
     * Remove a staff member
     */
    public function destroy(ProviderStaff $staff)
    {
        if ($staff->provider_id !== Auth::user()->provider->id) {
            abort(403, 'Unauthorized.');
        }

        $staff->delete();

        return redirect()->route('provider.staff.index')
            ->with('success', 'Staff removed successfully.');
    }

    /**
     * Get the maximum number of staff allowed for the provider's plan
     */
    private function getMaxStaff($provider): int
    {
        $subscription = $provider->activeSubscription()->first();
        $plan = $subscription ? $subscription->plan : null;

        if (!$plan) {
            return 1;
        }

        if (isset($plan->limits['max_staff'])) {
            return (int) $plan->limits['max_staff'];
        }

        return match ($plan->slug) {
            'free' => 1,
            'professional' => 5,
            'business' => 20,
            'enterprise' => -1,
            default => 1,
        };
    }
}