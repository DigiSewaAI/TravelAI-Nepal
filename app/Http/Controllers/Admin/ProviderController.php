<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\VerificationDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderController extends Controller
{
    public function index()
    {
        $providers = Provider::with(['user', 'types', 'documents'])
            ->withCount(['services', 'bookings'])
            ->latest()
            ->paginate(20);

        return view('admin.providers.index', compact('providers'));
    }

    public function show(Provider $provider)
    {
        $provider->load(['user', 'types', 'documents', 'services']);
        return view('admin.providers.show', compact('provider'));
    }

    public function verify(Request $request, Provider $provider)
    {
        $request->validate([
            'status' => 'required|in:verified,rejected,pending',
        ]);

        $provider->verification_status = $request->status;
        $provider->save();

        // Update all documents status if verified
        if ($request->status === 'verified') {
            $provider->documents()->update(['status' => 'approved']);
        }

        return back()->with('success', 'Provider verification status updated.');
    }

    public function toggleActive(Provider $provider)
    {
        $provider->is_active = !$provider->is_active;
        $provider->save();

        return back()->with('success', 'Provider status updated.');
    }

    public function destroy(Provider $provider)
    {
        // Delete associated documents
        foreach ($provider->documents as $doc) {
            \Storage::disk('public')->delete($doc->file_path);
        }

        $provider->delete();

        return redirect()->route('admin.providers.index')->with('success', 'Provider deleted.');
    }
}