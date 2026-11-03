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

        // Notify admins: new agency registered
        try {
            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AdminAlertNotification(
                    'New Agency Registered',
                    $agency->name . ' (' . $agency->email . ')',
                    'agency',
                    url('/admin/dashboard'),
                    $agency->id
                ));
            }
        } catch (\Throwable $e) {
            \Log::warning('Admin agency-reg notify failed', ['agency_id' => $agency->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('agency.dashboard');
    }
}