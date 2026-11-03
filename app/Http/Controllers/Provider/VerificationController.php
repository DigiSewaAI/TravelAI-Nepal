<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\VerificationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $documents = $provider->documents;
        return view('provider.verification.index', compact('provider', 'documents'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $request->validate([
            'type' => 'required|in:business_registration,tourism_license,guide_license,id_card,other',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('document')->store('verification_docs', 'public');

        VerificationDocument::create([
            'provider_id' => $provider->id,
            'type' => $request->type,
            'file_path' => $path,
            'status' => 'pending',
        ]);

        // Notify admins: verification document submitted
        try {
            $__adminUrl = \Illuminate\Support\Facades\Route::has('admin.providers.show')
                ? route('admin.providers.show', $provider->id)
                : url('/admin/dashboard');
            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\AdminAlertNotification(
                    'Verification Document Submitted',
                    $provider->name . ' uploaded ' . $request->type,
                    'verification',
                    $__adminUrl,
                    $provider->id
                ));
            }
        } catch (\Throwable $e) {
            \Log::warning('Admin verification notify failed', ['provider_id' => $provider->id, 'error' => $e->getMessage()]);
        }

        return back()->with('success', 'Document uploaded successfully. It will be reviewed shortly.');
    }

    public function destroy(VerificationDocument $document)
    {
        $user = Auth::user();
        $provider = $user->ownProvider();

        if ($document->provider_id !== $provider->id) {
            abort(403);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }
}