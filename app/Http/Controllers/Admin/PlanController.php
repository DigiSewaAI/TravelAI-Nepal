<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('price_monthly')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_monthly' => 'nullable|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'requires_contact' => 'boolean',
            'limits' => 'nullable|string',
            'features' => 'nullable|string',
        ]);

        // Decode JSON strings back to arrays (Model casts will encode them back to JSON)
        $limits = !empty($validated['limits']) ? json_decode($validated['limits'], true) : null;
        $features = !empty($validated['features']) ? json_decode($validated['features'], true) : null;

        // Safety check for invalid JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['limits' => 'Invalid JSON format for limits or features.'])->withInput();
        }

        $plan->update([
            'name' => $validated['name'],
            'price_monthly' => $validated['price_monthly'] ?? 0,
            'price_yearly' => $validated['price_yearly'] ?? 0,
            'requires_contact' => $request->has('requires_contact'),
            'limits' => $limits,
            'features' => $features,
        ]);

        return redirect()->route('admin.plans.index')->with('success', 'Plan updated successfully.');
    }
}