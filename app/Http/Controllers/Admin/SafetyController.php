<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TravelSafetyIncident;
use App\Models\SafetySource;
use App\Models\SafetyAuditLog;
use App\Services\Safety\SafetyStatusService;
use App\Services\Safety\RiskScoringService;
use App\Jobs\Safety\UpdateSafetyStatusesJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Admin safety dashboard
     */
    public function dashboard()
    {
        $summary = $this->statusService->getDashboardSummary();

        // Recent incidents
        $recentIncidents = TravelSafetyIncident::with('sources')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Source health
        $sources = SafetySource::all();
        $sourceStats = [
            'total' => $sources->count(),
            'enabled' => $sources->where('enabled', true)->count(),
            'healthy' => $sources->whereNotNull('last_success_at')
                ->where('last_success_at', '>=', now()->subHours(24))->count(),
            'failed' => $sources->whereNotNull('last_error')->count(),
        ];

        // Recent audit logs
        $auditLogs = SafetyAuditLog::with(['incident', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Alert statistics
        $alertStats = DB::table('traveler_safety_alerts')
            ->selectRaw('COUNT(*) as total, COUNT(CASE WHEN read_at IS NULL THEN 1 END) as unread')
            ->first();

        return view('admin.safety.dashboard', compact(
            'summary',
            'recentIncidents',
            'sourceStats',
            'auditLogs',
            'alertStats'
        ));
    }

    /**
     * Incident management
     */
    public function incidents(Request $request)
    {
        $query = TravelSafetyIncident::with('sources');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('severity') && $request->severity) {
            $query->where('severity', $request->severity);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('location_name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $incidents = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.safety.incidents', compact('incidents'));
    }

    /**
     * Verify an incident
     */
    public function verify(Request $request, int $id)
    {
        $incident = TravelSafetyIncident::findOrFail($id);
        $incident->status = 'verified';
        $incident->last_verified_at = now();
        $incident->confidence_score = min(1, $incident->confidence_score + 0.2);
        $incident->save();

        // Log audit
        SafetyAuditLog::create([
            'incident_id' => $incident->id,
            'action' => 'admin_verified',
            'old_values' => ['status' => $incident->getOriginal('status')],
            'new_values' => ['status' => 'verified'],
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reason' => $request->reason ?? 'Admin verification',
        ]);

        // Update safety statuses
        UpdateSafetyStatusesJob::dispatch();

        return redirect()->back()->with('success', 'Incident verified successfully');
    }

    /**
     * Override incident status
     */
    public function override(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:active,resolved,false_positive',
            'reason' => 'required|string|min:10',
        ]);

        $incident = TravelSafetyIncident::findOrFail($id);
        $oldStatus = $incident->status;
        $incident->status = $request->status;
        $incident->save();

        // Log audit
        SafetyAuditLog::create([
            'incident_id' => $incident->id,
            'action' => 'admin_override',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $request->status],
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reason' => $request->reason,
        ]);

        // Update safety statuses
        UpdateSafetyStatusesJob::dispatch();

        return redirect()->back()->with('success', 'Incident status overridden successfully');
    }

    /**
     * Merge duplicate incidents
     */
    public function merge(Request $request, int $primaryId)
    {
        $request->validate([
            'duplicate_ids' => 'required|array|min:1',
            'duplicate_ids.*' => 'exists:travel_safety_incidents,id',
        ]);

        $primary = TravelSafetyIncident::findOrFail($primaryId);

        DB::transaction(function () use ($request, $primary) {
            foreach ($request->duplicate_ids as $dupId) {
                $duplicate = TravelSafetyIncident::find($dupId);
                if ($duplicate && $duplicate->id !== $primary->id) {
                    // Move sources to primary
                    foreach ($duplicate->sources as $source) {
                        $primary->sources()->syncWithoutDetaching([$source->id => [
                            'source_url' => $source->pivot->source_url,
                            'source_title' => $source->pivot->source_title,
                            'published_at' => $source->pivot->published_at,
                            'source_reliability' => $source->pivot->source_reliability,
                            'evidence_text' => $source->pivot->evidence_text,
                            'content_hash' => $source->pivot->content_hash,
                        ]]);
                    }

                    // Move affected entities
                    foreach ($duplicate->waypoints as $wp) {
                        $primary->waypoints()->syncWithoutDetaching([$wp->id => [
                            'distance' => $wp->pivot->distance,
                            'match_type' => $wp->pivot->match_type,
                            'confidence' => $wp->pivot->confidence,
                        ]]);
                    }

                    foreach ($duplicate->routes as $route) {
                        $primary->routes()->syncWithoutDetaching([$route->id => [
                            'distance' => $route->pivot->distance,
                            'match_type' => $route->pivot->match_type,
                            'confidence' => $route->pivot->confidence,
                        ]]);
                    }

                    foreach ($duplicate->treks as $trek) {
                        $primary->treks()->syncWithoutDetaching([$trek->id => [
                            'distance' => $trek->pivot->distance,
                            'match_type' => $trek->pivot->match_type,
                            'confidence' => $trek->pivot->confidence,
                        ]]);
                    }

                    // Delete duplicate
                    $duplicate->status = 'false_positive';
                    $duplicate->save();

                    SafetyAuditLog::create([
                        'incident_id' => $duplicate->id,
                        'action' => 'merged',
                        'old_values' => ['merged_into' => null],
                        'new_values' => ['merged_into' => $primary->id],
                        'user_id' => auth()->id(),
                        'reason' => 'Duplicate merged into primary incident',
                    ]);

                    SafetyAuditLog::create([
                        'incident_id' => $primary->id,
                        'action' => 'merged_with',
                        'old_values' => ['merged_ids' => null],
                        'new_values' => ['merged_ids' => $dupId],
                        'user_id' => auth()->id(),
                        'reason' => 'Merged duplicate incident',
                    ]);
                }
            }

            // Update primary confidence
            $primary->confidence_score = min(1, $primary->confidence_score + 0.1);
            $primary->save();
        });

        // Update safety statuses
        UpdateSafetyStatusesJob::dispatch();

        return redirect()->back()->with('success', 'Incidents merged successfully');
    }

    /**
     * Source management
     */
    public function sources()
    {
        $sources = SafetySource::orderBy('source_category')
            ->orderBy('name')
            ->get();

        return view('admin.safety.sources', compact('sources'));
    }

    /**
     * Create or update source
     */
    public function storeSource(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:rss,json,html',
            'feed_url' => 'required|url',
            'source_category' => 'required|in:official,institutional,news,other',
            'reliability_score' => 'required|numeric|min:0|max:1',
            'fetch_interval' => 'required|integer|min:5|max:1440',
            'enabled' => 'boolean',
        ]);

        $source = SafetySource::create($request->all());

        return redirect()->back()->with('success', 'Source created successfully');
    }

    /**
     * Toggle source enabled status
     */
    public function toggleSource(int $id)
    {
        $source = SafetySource::findOrFail($id);
        $source->enabled = !$source->enabled;
        $source->save();

        return redirect()->back()->with('success', 'Source toggled successfully');
    }

    /**
     * Audit log
     */
    public function audit(Request $request)
    {
        $query = SafetyAuditLog::with(['incident', 'user']);

        if ($request->has('incident_id')) {
            $query->where('incident_id', $request->incident_id);
        }

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.safety.audit', compact('logs'));
    }
}