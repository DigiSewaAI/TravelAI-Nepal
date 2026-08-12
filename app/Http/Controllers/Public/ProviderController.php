<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderType;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $query = Provider::with(['types', 'services'])
            ->where('is_active', true)
            ->where('verification_status', 'verified')
            ->whereHas('user', function ($q) {
                $q->where('role', '!=', 'super_admin'); // Super Admin exclude
            });

        // Filter by provider type
        if ($request->filled('type')) {
            $query->whereHas('types', function ($q) use ($request) {
                $q->where('slug', $request->type);
            });
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 🔥 Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'latest':
                $query->latest();
                break;
            case 'most_services':
                $query->withCount('services')->orderBy('services_count', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
        }

        $providers = $query->paginate(12);
        $providerTypes = ProviderType::all();

        return view('public.providers.index', compact('providers', 'providerTypes'));
    }

    public function show(Provider $provider)
    {
        $provider->load(['types', 'services' => function ($q) {
            $q->where('status', 'active')->with(['category', 'reviews']);
        }]);

        return view('public.providers.show', compact('provider'));
    }
}