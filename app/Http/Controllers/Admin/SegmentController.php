<?php
namespace App\Http\Controllers\Admin;

use App\Models\Route;
use App\Models\Waypoint;
use App\Models\RouteSegment;
use Illuminate\Http\Request;

class SegmentController extends \App\Http\Controllers\Controller
{
    public function index()
    {
        $segments = RouteSegment::with(['route', 'fromWaypoint', 'toWaypoint'])
            ->orderBy('route_id')
            ->orderBy('sequence')
            ->get();
        return view('admin.segments.index', compact('segments'));
    }

    public function create()
    {
        $routes = Route::orderBy('name')->pluck('name', 'id');
        $waypoints = Waypoint::orderBy('name')->pluck('name', 'id');
        return view('admin.segments.create', compact('routes', 'waypoints'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:routes,id',
            'from_waypoint_id' => 'required|exists:waypoints,id|different:to_waypoint_id',
            'to_waypoint_id' => 'required|exists:waypoints,id|different:from_waypoint_id',
            'sequence' => 'required|integer|min:0',
            'distance_km' => 'required|numeric|min:0',
            'estimated_time_hours' => 'nullable|numeric|min:0',
            'elevation_gain_m' => 'nullable|integer|min:0',
            'elevation_loss_m' => 'nullable|integer|min:0',
        ]);

        RouteSegment::create($validated);
        return redirect()->route('admin.segments.index')->with('success', 'Segment created.');
    }

    public function edit(RouteSegment $segment)
    {
        $routes = Route::orderBy('name')->pluck('name', 'id');
        $waypoints = Waypoint::orderBy('name')->pluck('name', 'id');
        return view('admin.segments.edit', compact('segment', 'routes', 'waypoints'));
    }

    public function update(Request $request, RouteSegment $segment)
    {
        $validated = $request->validate([
            'route_id' => 'required|exists:routes,id',
            'from_waypoint_id' => 'required|exists:waypoints,id|different:to_waypoint_id',
            'to_waypoint_id' => 'required|exists:waypoints,id|different:from_waypoint_id',
            'sequence' => 'required|integer|min:0',
            'distance_km' => 'required|numeric|min:0',
            'estimated_time_hours' => 'nullable|numeric|min:0',
            'elevation_gain_m' => 'nullable|integer|min:0',
            'elevation_loss_m' => 'nullable|integer|min:0',
        ]);

        $segment->update($validated);
        return redirect()->route('admin.segments.index')->with('success', 'Segment updated.');
    }

    public function destroy(RouteSegment $segment)
    {
        $segment->delete();
        return redirect()->route('admin.segments.index')->with('success', 'Segment deleted.');
    }
}