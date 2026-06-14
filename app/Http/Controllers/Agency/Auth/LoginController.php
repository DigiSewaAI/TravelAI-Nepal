<?php

namespace App\Http\Controllers\Agency\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // कुनै __construct() छैन – मिडलवेयर रूट मार्फत लागू हुन्छ

    public function showLoginForm()
    {
        return view('agency.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::guard('agency')->attempt($credentials, $request->filled('remember'))) {
            return redirect()->intended(route('agency.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
    }

    public function logout()
    {
        Auth::guard('agency')->logout();
        return redirect()->route('agency.login');
    }
}