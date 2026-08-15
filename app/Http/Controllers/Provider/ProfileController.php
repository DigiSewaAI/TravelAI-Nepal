<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
    ]);

    // Logo upload
    if ($request->hasFile('logo')) {
        if ($provider->logo_url) {
            Storage::disk('public')->delete($provider->logo_url);
        }
        $path = $request->file('logo')->store('providers/logos', 'public');
        $validated['logo_url'] = $path;
    }
    unset($validated['logo']); // ✅ logo field हटाउने (column होइन)

    // 🔥 Cover image upload
    if ($request->hasFile('cover_image')) {
        if ($provider->cover_image) {
            Storage::disk('public')->delete($provider->cover_image);
        }
        $path = $request->file('cover_image')->store('providers/covers', 'public');
        $validated['cover_image'] = $path; // ✅ column मा save गर्ने
    }
    // ❌ unset($validated['cover_image']); // ✅ यो line **पूरै हटाउनुहोस्**

    $provider->update($validated);

    return redirect()->route('provider.profile')
        ->with('success', 'Profile updated successfully!');
}
}