<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $provider = $user->providers()->first();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        return view('provider.profile', compact('provider'));
    }

    public function edit()
    {
        $user = Auth::user();
        $provider = $user->providers()->first();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        return view('provider.profile-edit', compact('provider'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $provider = $user->providers()->first();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:2000',
        ]);

        $provider->update($validated);

        return redirect()->route('provider.profile')
            ->with('success', 'Profile updated successfully.');
    }
}