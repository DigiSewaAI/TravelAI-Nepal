<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RescueProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RescueDocumentsController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'pending');
        
        $providers = RescueProvider::with('provider')->get();
        $documents = collect();

        foreach ($providers as $rp) {
            foreach (($rp->documents ?? []) as $index => $doc) {
                // Filter logic
                $docStatus = $doc['status'] ?? 'pending';
                if ($statusFilter !== 'all' && $docStatus !== $statusFilter) {
                    continue;
                }

                $documents->push([
                    'provider_id'    => $rp->provider_id,
                    'provider_name'  => $rp->provider->name ?? 'Unknown Provider',
                    'doc_index'      => $index,
                    'title'          => $doc['title'] ?? 'Untitled',
                    'issuer'         => $doc['issuer'] ?? '—',
                    'expiry'         => $doc['expiry'] ?? '—',
                    'uploaded_at'    => $doc['uploaded_at'] ?? null,
                    'status'         => $docStatus,
                    'has_file'       => !empty($doc['file_path']),
                ]);
            }
        }

        // Sort by pending first, then newest
        $documents = $documents->sortByDesc(function ($doc) {
            return ($doc['status'] === 'pending' ? 1 : 0) . $doc['uploaded_at'];
        });

        return view('admin.rescue.documents.index', compact('documents', 'statusFilter'));
    }

    public function download($providerId, $index)
    {
        $rp = RescueProvider::where('provider_id', $providerId)->firstOrFail();
        $doc = $rp->documents[$index] ?? abort(404, 'Document not found.');

        if (empty($doc['file_path'])) {
            abort(404, 'File path is missing.');
        }

        $disk = $doc['file_disk'] ?? config('rescue.documents_disk', 'local');

        if (!Storage::disk($disk)->exists($doc['file_path'])) {
            abort(404, 'File missing from storage.');
        }

        Log::info("Admin downloaded doc", ['admin_id' => Auth::id(), 'provider_id' => $providerId, 'doc' => $doc['title']]);

        return Storage::disk($disk)->download($doc['file_path'], $doc['original_name'] ?? 'document');
    }

    public function verify($providerId, $index)
    {
        $rp = RescueProvider::where('provider_id', $providerId)->firstOrFail();
        $docs = $rp->documents ?? [];

        if (!isset($docs[$index])) {
            abort(404);
        }

        $docs[$index]['status'] = 'verified';
        $docs[$index]['verified_by'] = Auth::id();
        $docs[$index]['verified_at'] = now()->toIso8601String();

        $rp->update(['documents' => $docs]);

        Log::info("Admin verified document", ['admin_id' => Auth::id(), 'provider_id' => $providerId, 'doc' => $docs[$index]['title']]);

        return back()->with('success', "Document '{$docs[$index]['title']}' verified successfully.");
    }

    public function reject(Request $request, $providerId, $index)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $rp = RescueProvider::where('provider_id', $providerId)->firstOrFail();
        $docs = $rp->documents ?? [];

        if (!isset($docs[$index])) {
            abort(404);
        }

        $docs[$index]['status'] = 'action_needed';
        $docs[$index]['rejection_reason'] = $request->rejection_reason;
        $docs[$index]['reviewed_by'] = Auth::id();
        $docs[$index]['reviewed_at'] = now()->toIso8601String();

        $rp->update(['documents' => $docs]);

        Log::info("Admin rejected document", ['admin_id' => Auth::id(), 'provider_id' => $providerId, 'doc' => $docs[$index]['title']]);

        return back()->with('success', "Document rejected — provider will be notified.");
    }
}