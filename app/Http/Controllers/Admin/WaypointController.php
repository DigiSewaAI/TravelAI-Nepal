<?php
namespace App\Http\Controllers\Admin;

use App\Models\Waypoint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WaypointController extends \App\Http\Controllers\Controller
{
    public function index()
    {
        $waypoints = Waypoint::orderBy('name')->get();
        return view('admin.waypoints.index', compact('waypoints'));
    }

    public function create()
    {
        return view('admin.waypoints.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:waypoints',
            'type' => 'required|in:village,checkpoint,landmark,pass,peak,trailhead',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'altitude' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'metadata' => 'nullable|json',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        // convert metadata to array if provided
        if (!empty($validated['metadata'])) {
            $validated['metadata'] = json_decode($validated['metadata'], true);
        }

        Waypoint::create($validated);
        return redirect()->route('admin.waypoints.index')->with('success', 'Waypoint created.');
    }

    public function edit(Waypoint $waypoint)
    {
        return view('admin.waypoints.edit', compact('waypoint'));
    }

    public function update(Request $request, Waypoint $waypoint)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:waypoints,name,'.$waypoint->id,
            'type' => 'required|in:village,checkpoint,landmark,pass,peak,trailhead',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'altitude' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'metadata' => 'nullable|json',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        if (!empty($validated['metadata'])) {
            $validated['metadata'] = json_decode($validated['metadata'], true);
        }

        $waypoint->update($validated);
        return redirect()->route('admin.waypoints.index')->with('success', 'Waypoint updated.');
    }

    public function destroy(Waypoint $waypoint)
    {
        $waypoint->delete();
        return redirect()->route('admin.waypoints.index')->with('success', 'Waypoint deleted.');
    }
}