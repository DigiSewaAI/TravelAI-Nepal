<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Services\AdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    public function __construct(private AdService $adService) {}

    /**
     * Queue — pending + list all ads.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending_review');

        $query = Ad::with('provider');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $ads = $query->latest()->paginate(20);

        $counts = [
            'pending_review'  => Ad::where('status', 'pending_review')->count(),
            'active'          => Ad::where('status', 'active')->count(),
            'rejected'        => Ad::where('status', 'rejected')->count(),
            'expired'         => Ad::where('status', 'expired')->count(),
        ];

        return view('admin.ads.index', compact('ads', 'counts', 'status'));
    }

    /**
     * Ad detail + payment proof.
     */
    public function show(Ad $ad)
    {
        $ad->load(['provider', 'payments', 'verifiedBy']);
        return view('admin.ads.show', compact('ad'));
    }

    /**
     * Approve — verify payment + activate ad.
     */
    public function approve(Ad $ad)
    {
        $ad->update([
            'status'              => 'active',
            'payment_status'      => 'verified',
            'payment_verified_at' => now(),
            'verified_by'         => Auth::id(),
            'verified_at'         => now(),
            'start_date'          => now(),
            'end_date'            => now()->addDays($ad->duration_days),
        ]);

        $ad->payments()->update([
            'status'      => 'verified',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $this->adService->clearCache();

        return back()->with('success', __('messages.ads_approved_success'));
    }

    /**
     * Reject — with reason.
     */
    public function reject(Request $request, Ad $ad)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $ad->update([
            'status'           => 'rejected',
            'payment_status'   => 'rejected',
            'verified_by'      => Auth::id(),
            'verified_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        $ad->payments()->update([
            'status'      => 'rejected',
            'admin_notes' => $validated['rejection_reason'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        $this->adService->clearCache();

        return back()->with('success', __('messages.ads_rejected_success'));
    }

    /**
     * Toggle active state (pause/resume).
     */
    public function toggle(Ad $ad)
    {
        if ($ad->status === 'active') {
            $ad->update(['status' => 'expired']); // paused
        } elseif ($ad->status === 'expired' && !$ad->isExpired()) {
            $ad->update(['status' => 'active']);
        }

        $this->adService->clearCache();
        return back()->with('success', __('messages.ads_toggled_success'));
    }

    /**
     * Delete ad (soft).
     */
    public function destroy(Ad $ad)
    {
        $ad->delete();
        $this->adService->clearCache();
        return back()->with('success', __('messages.ads_deleted_success'));
    }

    /**
     * Analytics per ad.
     */
    public function analytics(Ad $ad)
    {
        $ad->load(['impressionsLog', 'clicksLog']);

        $last30 = [
            'impressions' => $ad->impressionsLog()->where('viewed_at', '>=', now()->subDays(30))->count(),
            'clicks'      => $ad->clicksLog()->where('clicked_at', '>=', now()->subDays(30))->count(),
        ];

        return view('admin.ads.analytics', compact('ad', 'last30'));
    }
}