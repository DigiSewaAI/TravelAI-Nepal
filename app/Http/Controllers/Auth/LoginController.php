<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Redirect based on role
            $user = Auth::user();
            if ($user->isSuperAdmin()) {
    return redirect()->intended(route('admin.dashboard'));
} elseif ($user->isProviderOwner() || $user->role === 'manager' || $user->role === 'staff') {
    // प्रोभाइडर प्रोफाइल छ कि जाँच गरौं
    if (!$user->provider) {
        // सत्र बन्द गरौं वा लगआउट नगरी फिर्ता पठाऔं
        auth()->logout();
        return redirect()->route('login')->withErrors([
            'email' => 'Your account is not linked to a provider profile. Please contact support.'
        ]);
    }
    return redirect()->intended(route('provider.dashboard'));
} else {
    return redirect()->intended(route('traveler.dashboard'));
}
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Logout the user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}