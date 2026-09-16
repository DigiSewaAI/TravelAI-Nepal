<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

            $user = Auth::user();

            Log::info('User logged in', [
                'user_id' => $user->id,
                'role'    => $user->role,
            ]);

            // Redirect based on role
            if ($user->isSuperAdmin()) {
    return redirect()->intended(route('admin.dashboard'));
} elseif ($user->isProviderOwner() || $user->role === 'manager' || $user->role === 'staff') {
    // प्रोभाइडर प्रोफाइल छ कि जाँच गरौं
        if (!$user->provider) {
        Log::warning('Login blocked: provider profile missing', [
            'user_id' => $user->id,
            'role'    => $user->role,
        ]);
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

                Log::warning('Failed login attempt', [
            'email_hash' => hash('sha256', strtolower(trim($request->input('email', '')))),
            'ip_hash'    => hash('sha256', $request->ip() ?? 'unknown'),
        ]);

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