<?php

namespace App\Http\Controllers;

use App\Models\TravelSafetyIncident;
use App\Models\Waypoint;
use App\Models\Route;
use App\Models\Trek;
use App\Models\Location;
use App\Services\Safety\SafetyStatusService;
use App\Services\Safety\RiskScoringService;
use Illuminate\Http\Request;

class SafetyController extends Controller
{
    protected $statusService;
    protected $riskService;

    public function __construct(SafetyStatusService $statusService, RiskScoringService $riskService)
    {
        $this->statusService = $statusService;
        $this->riskService = $riskService;
    }

    /**
     * Public safety overview page
     */
    public function index()
    {
        $summary = $this->statusService->getDashboardSummary();

        // Get active incidents with location
        $incidents = TravelSafetyIncident::whereIn('status', ['active', 'verified'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('severity', 'desc')
            ->orderBy('reported_at', 'desc')
            ->limit(20)
            ->get();

        // Get affected destinations
        $affectedWaypoints = Waypoint::whereNotNull('safety_status')
            ->where('safety_status', '!=', 'normal')
            ->where('safety_status', '!=', 'unknown')
            ->with('safetyIncidents')
            ->limit(10)
            ->get();

        $affectedTreks = Trek::whereNotNull('safety_status')
            ->where('safety_status', '!=', 'normal')
            ->where('safety_status', '!=', 'unknown')
            ->with('safetyIncidents')
            ->limit(10)
            ->get();

        return view('safety.index', compact(
            'summary',
            'incidents',
            'affectedWaypoints',
            'affectedTreks'
        ));
    }

    /**
     * Destination safety detail page
     */
    public function destination(Request $request, string $slug)
    {
        // Try to find as Trek, Waypoint, or Route
        $entity = Trek::where('slug', $slug)->first();
        if (!$entity) {
            $entity = Waypoint::where('slug', $slug)->first();
        }
        if (!$entity) {
            $entity = Route::where('slug', $slug)->first();
        }
        if (!$entity) {
            abort(404, 'Destination not found');
        }

        $status = $this->statusService->getStatusForEntity($entity);
        $incidents = $entity->safetyIncidents()
            ->whereIn('status', ['active', 'verified'])
            ->with('sources')
            ->get();

        return view('safety.destination', compact('entity', 'status', 'incidents'));
    }

    /**
     * Incident detail page
     */
    public function incident(int $id)
    {
        $incident = TravelSafetyIncident::with(['sources', 'waypoints', 'routes', 'treks'])
            ->findOrFail($id);

        // Get affected entities
        $affectedWaypoints = $incident->waypoints;
        $affectedRoutes = $incident->routes;
        $affectedTreks = $incident->treks;

        return view('safety.incident', compact(
            'incident',
            'affectedWaypoints',
            'affectedRoutes',
            'affectedTreks'
        ));
    }

    /**
     * API endpoint for map markers
     */
    public function markers(Request $request)
    {
        $incidents = TravelSafetyIncident::whereIn('status', ['active', 'verified'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($request->has('severity'), function ($query) use ($request) {
                return $query->where('severity', $request->severity);
            })
            ->get();

        $markers = $incidents->map(function ($incident) {
            return [
                'id' => $incident->id,
                'title' => $incident->title,
                'latitude' => $incident->latitude,
                'longitude' => $incident->longitude,
                'severity' => $incident->severity,
                'status' => $incident->status,
                'type' => $incident->incident_type,
                'location' => $incident->location_name,
                'reported_at' => $incident->reported_at?->toIso8601String(),
                'last_verified' => $incident->last_verified_at?->toIso8601String(),
                'confidence' => $incident->confidence_score,
                'affected_radius' => $incident->affected_radius ?? 5000,
                'color' => $this->getSeverityColor($incident->severity),
                'url' => route('safety.incident', $incident->id),
            ];
        });

        return response()->json($markers);
    }

    protected function getSeverityColor(?string $severity): string
    {
        return match ($severity) {
            'critical' => '#dc3545',
            'high' => '#fd7e14',
            'moderate' => '#ffc107',
            'low' => '#28a745',
            default => '#6c757d',
        };
    }
}