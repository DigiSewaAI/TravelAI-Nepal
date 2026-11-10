<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\RescueProvider;
use App\Models\SosAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RescueDashboardController extends Controller
{
    private function getProvider(): ?\App\Models\Provider
    {
        $user = Auth::user();
        return $user?->provider ?? \App\Models\Provider::where('user_id', $user?->id)->first();
    }

    private function getRescueProvider(): ?RescueProvider
    {
        $provider = $this->getProvider();
        if (!$provider) return null;
        return RescueProvider::where('provider_id', $provider->id)->first();
    }

    private function sharedData(): array
    {
        $provider = $this->getProvider();
        $rescueProvider = $this->getRescueProvider();

        $liveSos = SosAlert::where('provider_id', $provider?->id)
            ->whereIn('status', ['pending', 'sent', 'acknowledged', 'responding'])
            ->count();

        // Fetch real fleet and mission count
        $activeMissions = \App\Models\RescueMission::where('provider_id', $provider?->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        return [
            'provider'              => $provider,
            'rescueProvider'        => $rescueProvider,
            'liveSosCount'          => $liveSos,
            'activeMissionsCount'   => $activeMissions,
        ];
    }

    public function index()
    {
        $shared = $this->sharedData();
        $provider = $shared['provider'];

        $sosQueue = SosAlert::where('provider_id', $provider?->id)
            ->whereIn('status', ['pending', 'sent', 'acknowledged'])
            ->latest()
            ->take(10)
            ->get();

        return view('provider.rescue.dashboard', array_merge($shared, [
            'sosQueue' => $sosQueue,
        ]));
    }

    public function sosQueue()
    {
        // 🆕 Auto-mark rescue notifications as read when SOS page loads
        auth()->user()->unreadNotifications()
            ->where('data->type', 'rescue_dispatch')
            ->get()
            ->markAsRead();

        $shared = $this->sharedData();
        $provider = $shared['provider'];

        $alerts = SosAlert::where('provider_id', $provider?->id)
            ->whereIn('status', ['pending', 'sent', 'acknowledged', 'responding'])
            ->latest()
            ->paginate(15);

        return view('provider.rescue.sos', array_merge($shared, [
            'alerts' => $alerts,
        ]));
    }

    public function missions()
    {
        $shared = $this->sharedData();
        $provider = $shared['provider'];

        $missions = \App\Models\RescueMission::where('provider_id', $provider?->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['sos.traveler', 'fleet'])
            ->latest()
            ->get();

        return view('provider.rescue.missions', array_merge($shared, [
            'missions' => $missions,
        ]));
    }
        public function acceptSos(SosAlert $sos)
    {
        $provider = $this->getProvider();
        
        // 1. यदि अरू कसैले पहिले नै लक गरिसकेको छ भने रोक्ने
        $activeLock = \App\Models\RescueDispatchLock::where('sos_alert_id', $sos->id)
            ->where('expires_at', '>', now())
            ->where('provider_id', '!=', $provider->id)
            ->first();

        if ($activeLock) {
            return back()->with('error', 'This SOS has already been locked by another provider.');
        }

        // 2. आफ्नो लक बनाउने (६० सेकेन्डको)
        \App\Models\RescueDispatchLock::updateOrCreate(
            ['sos_alert_id' => $sos->id, 'provider_id' => $provider->id],
            ['expires_at' => now()->addSeconds(60)]
        );

        return back()->with('success', 'SOS Accepted! Please confirm dispatch within 60 seconds.');
    }

     public function confirmDispatch(SosAlert $sos)
    {
        $provider = $this->getProvider();

        $lock = \App\Models\RescueDispatchLock::where('sos_alert_id', $sos->id)
            ->where('provider_id', $provider->id)
            ->where('expires_at', '>', now())
            ->first();

        if (!$lock) {
            return back()->with('error', 'Your lock has expired. Please accept the SOS again.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($sos, $provider, $lock) {
                // 1. SOS लाई Lock गर्ने (ताकि अरूले एकैचोटि अपडेट गर्न नसकून्)
                $freshSos = SosAlert::lockForUpdate()->find($sos->id);

                // 2. SOS अझै pending वा sent छ कि जाँच्ने
                if (!in_array($freshSos->status, ['pending', 'sent'])) {
                    throw new \Exception('This SOS is already being handled by another provider.');
                }

                // 3. यो SOS को लागि मिसन पहिले नै बनिसकेको छ कि जाँच्ने
                if (\App\Models\RescueMission::where('sos_alert_id', $sos->id)->exists()) {
                    throw new \Exception('A rescue mission is already assigned to this SOS.');
                }

                // 4. मिसन क्रिएट गर्ने
                \App\Models\RescueMission::create([
                    'mission_number' => 'RSC-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'sos_alert_id'   => $sos->id,
                    'provider_id'    => $provider->id,
                    'status'         => 'accepted',
                    'pickup_lat'     => $sos->latitude,
                    'pickup_lng'     => $sos->longitude,
                ]);

                // 5. SOS अपडेट गर्ने
                $freshSos->update(['status' => 'responding']);

                // 6. लक डिलिट गर्ने
                $lock->delete();
            });

            return redirect()->route('provider.rescue.missions')->with('success', 'Dispatch Confirmed! Mission started.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
            public function updateMissionStatus(Request $request, \App\Models\RescueMission $mission)
    {
        $provider = $this->getProvider();
        if (!$provider || $mission->provider_id !== $provider->id) abort(403);

        if (in_array($mission->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Mission is already closed.');
        }

        $validated = $request->validate([
            'status' => 'required|in:accepted,en_route,on_scene,extracting,completed,cancelled',
        ]);

        $timestampMap = [
            'accepted'   => 'accepted_at',
            'en_route'   => 'en_route_at',
            'on_scene'   => 'on_scene_at',
            'completed'  => 'completed_at',
        ];

        $data = ['status' => $validated['status']];
        if (isset($timestampMap[$validated['status']])) {
            $data[$timestampMap[$validated['status']]] = now();
        }

        // 🆕 Sync linked SOS status
        \Illuminate\Support\Facades\DB::transaction(function () use ($mission, $validated, $data) {
            $mission->update($data);

            $sos = $mission->sos;
            if ($sos) {
                if ($validated['status'] === 'completed') {
                    $sos->update([
                        'status'      => 'resolved',
                        'resolved_at' => now(),
                    ]);
                } elseif ($validated['status'] === 'accepted' && $sos->status === 'pending') {
                    $sos->update(['status' => 'acknowledged']);
                } elseif (in_array($validated['status'], ['en_route', 'on_scene', 'extracting'])
                          && in_array($sos->status, ['pending', 'sent', 'acknowledged'])) {
                    $sos->update(['status' => 'responding']);
                } elseif ($validated['status'] === 'cancelled') {
                    $sos->update([
                        'status'      => 'false_alarm',
                        'resolved_at' => now(),
                    ]);
                }
            }
        });

        return back()->with('success', 'Mission status updated to ' . str_replace('_', ' ', $validated['status']) . '.');
    }

    public function gps()
    {
        return view('provider.rescue.gps', $this->sharedData());
    }

    public function audit()
    {
        return view('provider.rescue.audit', $this->sharedData());
    }

    public function fleet()
    {
        return view('provider.rescue.fleet', $this->sharedData());
    }

    public function staff()
    {
        return view('provider.rescue.staff', $this->sharedData());
    }

    public function coverage()
    {
        return view('provider.rescue.coverage', $this->sharedData());
    }

    public function updateCoverage(Request $request)
    {
        $rescueProvider = $this->getRescueProvider();
        if (!$rescueProvider) abort(403);

        $validated = $request->validate([
            'coverage_districts' => 'nullable|array',
            'coverage_districts.*' => 'string|max:50',
            'coverage_radius_km' => 'required|integer|min:10|max:500',
        ]);

        $rescueProvider->update([
            'coverage_districts' => $validated['coverage_districts'] ?? [],
            'coverage_radius_km' => $validated['coverage_radius_km'],
        ]);

        return back()->with('success', 'Coverage areas updated successfully.');
    }

    public function documents()
    {
        return view('provider.rescue.documents', $this->sharedData());
    }

    public function pricing()
    {
        return view('provider.rescue.pricing', $this->sharedData());
    }

    public function updatePricing(Request $request)
    {
        $rescueProvider = $this->getRescueProvider();
        if (!$rescueProvider) abort(403);

        $validated = $request->validate([
            'base_rate_npr'               => 'required|numeric|min:0',
            'per_km_rate_npr'             => 'required|numeric|min:0',
            'per_hour_rate_npr'           => 'required|numeric|min:0',
            'night_surcharge_pct'         => 'required|integer|min:0|max:100',
            'high_altitude_surcharge_pct' => 'required|integer|min:0|max:100',
        ]);

        $rescueProvider->update($validated);

        return back()->with('success', 'Pricing configuration updated successfully.');
    }

    public function storeStaff(Request $request)
    {
        $rescueProvider = $this->getRescueProvider();
        if (!$rescueProvider) abort(403);

        $staff = $rescueProvider->staff_members ?? [];
        $staff[] = $request->validate([
            'name'    => 'required|string|max:100',
            'role'    => 'required|string|max:50',
            'license' => 'nullable|string|max:50',
            'hours'   => 'nullable|string|max:50',
            'phone'   => 'required|string|max:20', // 🆕 Phone अनिवार्य
        ]);

        $rescueProvider->update(['staff_members' => $staff]);
        return back()->with('success', 'Staff member added successfully.');
    }
        public function updateStaff(Request $request, int $index)
    {
        $rescueProvider = $this->getRescueProvider();
        if (!$rescueProvider) abort(403);

        $staff = $rescueProvider->staff_members ?? [];
        if (!isset($staff[$index])) abort(404);

        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'role'    => 'required|string|max:50',
            'license' => 'nullable|string|max:50',
            'hours'   => 'nullable|string|max:50',
            'phone'   => 'required|string|max:20',
        ]);

        $staff[$index] = $validated;
        $rescueProvider->update(['staff_members' => $staff]);

        return back()->with('success', 'Staff member updated successfully.');
    }

    public function destroyStaff(int $index)
    {
        $rescueProvider = $this->getRescueProvider();
        if (!$rescueProvider) abort(403);

        $staff = $rescueProvider->staff_members ?? [];
        if (!isset($staff[$index])) abort(404);

        unset($staff[$index]);
        $staff = array_values($staff); // Re-index to prevent gaps

        $rescueProvider->update(['staff_members' => $staff]);

        return back()->with('success', 'Staff member removed successfully.');
    }

    public function storeInsurance(Request $request)
    {
        $rescueProvider = $this->getRescueProvider();
        if (!$rescueProvider) abort(403);

        $partners = $rescueProvider->insurance_partners ?? [];
        $partners[] = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'status' => 'required|string|in:active,pending',
        ]);

        $rescueProvider->update(['insurance_partners' => $partners]);
        return back()->with('success', 'Insurance partner added successfully.');
    }
        public function updateInsurance(Request $request, int $index)
    {
        $rp = $this->getRescueProvider();
        if (!$rp) abort(403);
        $partners = $rp->insurance_partners ?? [];
        if (!isset($partners[$index])) abort(404);

        $validated = $request->validate([
            'name'   => 'required|string|max:100',
            'type'   => 'required|string|max:50',
            'status' => 'required|string|in:active,pending',
        ]);

        $partners[$index] = $validated;
        $rp->update(['insurance_partners' => $partners]);
        return back()->with('success', 'Insurance partner updated successfully.');
    }

    public function destroyInsurance(int $index)
    {
        $rp = $this->getRescueProvider();
        if (!$rp) abort(403);
        $partners = $rp->insurance_partners ?? [];
        if (!isset($partners[$index])) abort(404);

        unset($partners[$index]);
        $rp->update(['insurance_partners' => array_values($partners)]);
        return back()->with('success', 'Insurance partner removed successfully.');
    }

    public function storeDocument(Request $request)
    {
        $rescueProvider = $this->getRescueProvider();
        if (!$rescueProvider) abort(403);

        $validated = $request->validate([
            'title'    => 'required|string|max:100',
            'issuer'   => 'required|string|max:100',
            'expiry'   => 'required|string|max:50',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:' . config('rescue.documents_max_kb', 10240),
        ]);

        // 🚨 SECURITY: Provider NEVER sets status — always pending
        $validated['status'] = 'pending';

        if ($request->hasFile('document')) {
            $disk = config('rescue.documents_disk', 'local');
            $file = $request->file('document');

            $validated['file_path']     = $file->store('rescue-documents/'.$rescueProvider->provider_id, $disk);
            $validated['file_disk']     = $disk;
            $validated['original_name'] = $file->getClientOriginalName();
            $validated['file_size']     = $file->getSize();
            $validated['mime_type']     = $file->getMimeType();
            $validated['uploaded_at']   = now()->toIso8601String();
        }

        $docs = $rescueProvider->documents ?? [];
        $docs[] = $validated;

        $rescueProvider->update(['documents' => $docs]);
        return back()->with('success', 'Document uploaded — pending admin review.');
    }
    public function updateDocument(Request $request, int $index)
    {
        $rp = $this->getRescueProvider();
        if (!$rp) abort(403);
        $docs = $rp->documents ?? [];
        if (!isset($docs[$index])) abort(404);

        $validated = $request->validate([
            'title'    => 'required|string|max:100',
            'issuer'   => 'required|string|max:100',
            'expiry'   => 'required|string|max:50',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:' . config('rescue.documents_max_kb', 10240),
        ]);

        $oldDoc = $docs[$index];

        // Preserve existing file metadata by default
        $validated['file_path']     = $oldDoc['file_path']     ?? null;
        $validated['file_disk']     = $oldDoc['file_disk']     ?? null;
        $validated['original_name'] = $oldDoc['original_name'] ?? null;
        $validated['file_size']     = $oldDoc['file_size']     ?? null;
        $validated['mime_type']     = $oldDoc['mime_type']     ?? null;
        $validated['uploaded_at']   = $oldDoc['uploaded_at']   ?? null;

        $newFilePath = null;
        $newDisk     = null;

        // 🚨 SAFE ORDER: store new file FIRST
        if ($request->hasFile('document')) {
            $disk = config('rescue.documents_disk', 'local');
            $file = $request->file('document');

            $newFilePath = $file->store('rescue-documents/'.$rp->provider_id, $disk);
            $newDisk     = $disk;

            $validated['file_path']     = $newFilePath;
            $validated['file_disk']     = $disk;
            $validated['original_name'] = $file->getClientOriginalName();
            $validated['file_size']     = $file->getSize();
            $validated['mime_type']     = $file->getMimeType();
            $validated['uploaded_at']   = now()->toIso8601String();
        }

        // 🚨 SECURITY: Any edit (metadata or file) resets status to pending
        $validated['status'] = 'pending';

        $docs[$index] = $validated;
        $rp->update(['documents' => $docs]);

        // 🚨 SAFE ORDER: delete old file ONLY after DB success
        if ($newFilePath && !empty($oldDoc['file_path'])) {
            $oldDisk = $oldDoc['file_disk'] ?? config('rescue.documents_disk', 'local');
            try {
                \Storage::disk($oldDisk)->delete($oldDoc['file_path']);
            } catch (\Throwable $e) {
                \Log::warning('Old document file delete failed', [
                    'path' => $oldDoc['file_path'], 'disk' => $oldDisk, 'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Document updated — pending admin re-review.');
    }

    public function destroyDocument(int $index)
    {
        $rp = $this->getRescueProvider();
        if (!$rp) abort(403);
        $docs = $rp->documents ?? [];
        if (!isset($docs[$index])) abort(404);

        $removed = $docs[$index];

        unset($docs[$index]);
        $rp->update(['documents' => array_values($docs)]);

        // Delete file AFTER DB update
        if (!empty($removed['file_path'])) {
            $disk = $removed['file_disk'] ?? config('rescue.documents_disk', 'local');
            try {
                \Storage::disk($disk)->delete($removed['file_path']);
            } catch (\Throwable $e) {
                \Log::warning('Document file delete failed', [
                    'path' => $removed['file_path'], 'disk' => $disk, 'error' => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Document deleted successfully.');
    }
    public function insurance()
    {
        return view('provider.rescue.insurance', $this->sharedData());
    }

    public function revenue()
    {
        return view('provider.rescue.revenue', $this->sharedData());
    }

    public function reports()
    {
        return view('provider.rescue.reports', $this->sharedData());
    }

    public function security()
    {
        return view('provider.rescue.security', $this->sharedData());
    }

    public function toggleAvailability(Request $request)
    {
        $rescueProvider = $this->getRescueProvider();
        if ($rescueProvider) {
            $rescueProvider->update(['is_available' => !$rescueProvider->is_available]);
        }
        return back()->with('success', $rescueProvider?->is_available ? 'You are now On Duty' : 'You are now Off Duty');
    }
        public function downloadDocument(int $index)
    {
        $rp = $this->getRescueProvider();
        if (!$rp) abort(403);
        $docs = $rp->documents ?? [];

        if (!isset($docs[$index])) abort(404);
        $doc = $docs[$index];

        if (empty($doc['file_path'])) abort(404, 'No file attached.');

        $disk = $doc['file_disk'] ?? config('rescue.documents_disk', 'local');
        if (!\Storage::disk($disk)->exists($doc['file_path'])) {
            abort(404, 'File not found on storage.');
        }

        return \Storage::disk($disk)->download(
            $doc['file_path'],
            $doc['original_name'] ?? 'document'
        );
    }
}