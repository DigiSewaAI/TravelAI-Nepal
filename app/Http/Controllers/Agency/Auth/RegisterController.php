<?php

namespace App\Http\Controllers\Agency\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    // कुनै __construct() छैन

    public function showRegistrationForm()
    {
        return view('agency.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agencies,email',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $agency = Agency::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        Auth::guard('agency')->login($agency);

        return redirect()->route('agency.dashboard');
    }
}