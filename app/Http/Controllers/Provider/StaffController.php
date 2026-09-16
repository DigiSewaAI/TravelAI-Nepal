<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderStaff;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StaffController extends Controller
{
    use AuthorizesRequests;

    /**
     * List all staff members for the current provider.
     */
    public function index()
    {
        $provider = $this->resolveProvider();

        $staff = ProviderStaff::where('provider_id', $provider->id)
                    ->with('user')
                    ->get();

        $maxStaff = $provider->max_staff;

        return view('provider.staff.index', compact('staff', 'maxStaff'));
    }

    /**
     * Show the form to add a new staff member.
     */
    public function create()
    {
        $this->authorize('create', ProviderStaff::class);

        $provider = $this->resolveProvider();
        $maxStaff = $provider->max_staff;
        $currentStaffCount = ProviderStaff::where('provider_id', $provider->id)->count();

        if ($maxStaff !== -1 && $maxStaff !== null && $currentStaffCount >= $maxStaff) {
            return redirect()->route('provider.staff.index')
                ->with('error', 'You have reached your staff limit. Please upgrade your plan to add more staff.');
        }

        return view('provider.staff.create');
    }

    /**
     * Store a new staff member (FIX-14: race-safe).
     */
    public function store(Request $request)
    {
        $this->authorize('create', ProviderStaff::class);

        $provider = $this->resolveProvider();

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'nullable|string|max:255',
        ]);

        $createdStaff = null;
        $limitReached = false;

        try {
            DB::transaction(function () use ($provider, $validated, &$createdStaff, &$limitReached) {
                // FIX-14: provider row lock serializes concurrent staff creation
                $lockedProvider = Provider::where('id', $provider->id)
                    ->lockForUpdate()
                    ->first();

                $max = $lockedProvider->max_staff;
                if ($max === -1 || $max === null) {
                    $max = PHP_INT_MAX;
                }

                $currentCount = ProviderStaff::where('provider_id', $lockedProvider->id)->count();

                if ($currentCount >= $max) {
                    $limitReached = true;
                    return;
                }

                $user = User::create([
                    'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role'     => 'staff',
                ]);

                $createdStaff = ProviderStaff::create([
                    'user_id'     => $user->id,
                    'provider_id' => $lockedProvider->id,
                    'role'        => $validated['role'] ?? 'staff',
                    'permissions' => [],
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Staff creation failed', [
                'provider_id' => $provider->id,
                'error_class' => get_class($e),
            ]);

            return back()
                ->withErrors(['error' => 'Could not create staff. Please try again.'])
                ->withInput();
        }

        if ($limitReached) {
            return redirect()->route('provider.staff.index')
                ->with('error', 'Staff limit reached. Please upgrade your plan.');
        }

        return redirect()->route('provider.staff.index')
            ->with('success', 'Staff member added successfully.');
    }

    /**
     * Show the edit form for a staff member.
     */
    public function edit(ProviderStaff $staff)
    {
        $this->resolveProvider();
        $this->authorize('update', $staff);

        return view('provider.staff.edit', compact('staff'));
    }

    /**
     * Update a staff member's details.
     */
    public function update(Request $request, ProviderStaff $staff)
    {
        $this->resolveProvider();
        $this->authorize('update', $staff);

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
     * Remove a staff member.
     */
    public function destroy(ProviderStaff $staff)
    {
        $this->resolveProvider();
        $this->authorize('delete', $staff);

        $staff->delete();

        return redirect()->route('provider.staff.index')
            ->with('success', 'Staff removed successfully.');
    }

    /**
     * FIX-14: Resolve the authenticated provider owner or abort 403.
     * Prevents null-dereference for non-provider-owner users.
     */
        protected function resolveProvider(): Provider
    {
        $user = Auth::user();

        // FIX-14: Explicit role check — owner-only (Super Admin not operational here)
        if (!$user || !$user->isProviderOwner()) {
            abort(403, 'Only provider owners can manage staff.');
        }

        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'Only provider owners can manage staff.');
        }

        return $provider;
    }
}