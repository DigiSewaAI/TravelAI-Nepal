<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\VerificationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    private function getProviderLayout($provider): string
    {
        $map = [
            'restaurant'         => 'layouts.provider-restaurant',
            'hotel'              => 'layouts.provider-hotel',
            'resort'             => 'layouts.provider-hotel',
            'lodge'              => 'layouts.provider-hotel',
            'homestay'           => 'layouts.provider-hotel',
            'transport-provider' => 'layouts.provider-transport',
            'shop-owner'         => 'layouts.provider-inventory',
            'rental-provider'    => 'layouts.provider-inventory',
            'wholesale-provider' => 'layouts.provider-inventory',
            'art-craft'          => 'layouts.provider-art-craft',
            'guide'              => 'layouts.provider-individual',
            'porter'             => 'layouts.provider-individual',
            'photographer'       => 'layouts.provider-individual',
            'money-changer'      => 'layouts.provider-money-changer',
            'rescue-service'     => 'layouts.provider-rescue',
        ];
        return $map[$provider?->types->first()?->slug] ?? 'layouts.provider';
    }

    public function index()
    {
        $user = Auth::user();
        $provider = $user->ownProvider();

        if (!$provider) {
            abort(403, 'No provider found.');
        }

        $documents = $provider->documents;

        return view('provider.verification.index', [
            'provider'        => $provider,
            'documents'       => $documents,
            'provider_layout' => $this->getProviderLayout($provider),
        ]);
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