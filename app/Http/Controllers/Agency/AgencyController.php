<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;  // ✅ यो लाइन थप्नुहोस्

class AgencyController extends Controller
{
    public function index()
    {
        $agencies = Agency::withCount(['treks', 'bookings'])->get();
        return view('agency.agencies.index', compact('agencies'));
    }

    public function create()
    {
        return view('agency.agencies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agencies',
            'password' => 'required|min:8',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'role' => 'nullable|in:agency,admin,super_admin',
        ]);
        $data['password'] = Hash::make($data['password']);
        Agency::create($data);
        return redirect()->route('agency.agencies.index')->with('success', 'Agency created successfully.');
    }

    public function show($id)
    {
        $agency = Agency::withCount(['treks', 'bookings'])
            ->with(['treks', 'bookings.trekker'])
            ->findOrFail($id);

        // Super admin ले मात्र हेर्न पाउने
        if (Auth::guard('agency')->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized');
        }

        return view('agency.agencies.show', compact('agency'));
    }

    public function edit($id)
    {
        $agency = Agency::findOrFail($id);
        return view('agency.agencies.edit', compact('agency'));
    }

    public function update(Request $request, $id)
    {
        $agency = Agency::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agencies,email,' . $id,
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'role' => 'nullable|in:agency,admin,super_admin',
        ]);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $agency->update($data);
        return redirect()->route('agency.agencies.index')->with('success', 'Agency updated successfully.');
    }

    public function destroy($id)
    {
        $agency = Agency::findOrFail($id);
        if ($agency->role === 'super_admin') {
            return back()->with('error', 'Cannot delete super admin.');
        }
        $agency->delete();
        return redirect()->route('agency.agencies.index')->with('success', 'Agency deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $agency = Agency::findOrFail($id);
        if ($agency->role === 'super_admin') {
            return back()->with('error', 'Cannot toggle super admin role.');
        }
        // Toggle role between 'agency' and 'admin'
        $agency->role = $agency->role === 'agency' ? 'admin' : 'agency';
        $agency->save();
        return back()->with('success', 'Role toggled successfully.');
    }
}