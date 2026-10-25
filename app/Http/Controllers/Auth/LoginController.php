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

            // PATH-3B B2: merge guest cart into user cart
            app(\App\Services\CartService::class)->mergeGuestCart($user->id);

            // ---------------------------------------------------------
            // FIX: Force redirect (intended() हटाइयो ताकि गलत पेजमा नजाओस्)
            // ---------------------------------------------------------
            
            if ($user->isSuperAdmin()) {
                return redirect()->route('admin.dashboard');
            } 
            
            // If the user has a Provider record OR is Staff, go to Provider Dashboard
            if ($user->getCurrentProvider()) {
                return redirect()->route('provider.dashboard');
            } 
            
            // Otherwise, send to Traveler Dashboard
            return redirect()->route('traveler.dashboard');
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