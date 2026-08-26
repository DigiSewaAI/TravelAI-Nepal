<?php
namespace App\Http\Controllers\Admin;

use App\Models\Route;
use App\Models\RouteCost;
use Illuminate\Http\Request;

class RouteCostController extends \App\Http\Controllers\Controller
{
    public function index()
    {
        $costs = RouteCost::with('route')->orderBy('route_id')->get();
        return view('admin.route-costs.index', compact('costs'));
    }

    public function create()
    {
        $routes = Route::orderBy('name')->pluck('name', 'id');
        return view('admin.route-costs.create', compact('routes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:routes,id',
            'type' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3|in:NPR,USD',
            'unit' => 'required|in:per_person,per_group,per_day,per_km',
            'is_mandatory' => 'sometimes|boolean',
            'metadata' => 'nullable|json',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
        ]);

        if (!empty($validated['metadata'])) {
            $validated['metadata'] = json_decode($validated['metadata'], true);
        }
        $validated['is_mandatory'] = $request->has('is_mandatory');

        RouteCost::create($validated);
        return redirect()->route('admin.route-costs.index')->with('success', 'Cost entry created.');
    }

    public function edit(RouteCost $routeCost)
    {
        $routes = Route::orderBy('name')->pluck('name', 'id');
        return view('admin.route-costs.edit', compact('routeCost', 'routes'));
    }

    public function update(Request $request, RouteCost $routeCost)
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:routes,id',
            'type' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3|in:NPR,USD',
            'unit' => 'required|in:per_person,per_group,per_day,per_km',
            'is_mandatory' => 'sometimes|boolean',
            'metadata' => 'nullable|json',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
        ]);

        if (!empty($validated['metadata'])) {
            $validated['metadata'] = json_decode($validated['metadata'], true);
        }
        $validated['is_mandatory'] = $request->has('is_mandatory');

        $routeCost->update($validated);
        return redirect()->route('admin.route-costs.index')->with('success', 'Cost updated.');
    }

    public function destroy(RouteCost $routeCost)
    {
        $routeCost->delete();
        return redirect()->route('admin.route-costs.index')->with('success', 'Cost deleted.');
    }
}