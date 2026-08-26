<?php
namespace App\Http\Controllers\Admin;

use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RouteController extends \App\Http\Controllers\Controller
{
    public function index()
    {
        $routes = Route::orderBy('name')->get();
        return view('admin.routes.index', compact('routes'));
    }

    public function create()
    {
        return view('admin.routes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:routes',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:easy,moderate,hard,extreme',
            'duration_days' => 'required|integer|min:1',
            'max_altitude' => 'nullable|integer|min:0',
            'season' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        Route::create($validated);
        return redirect()->route('admin.routes.index')->with('success', 'Route created.');
    }

    public function edit(Route $route)
    {
        return view('admin.routes.edit', compact('route'));
    }

    public function update(Request $request, Route $route)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:routes,name,'.$route->id,
            'description' => 'nullable|string',
            'difficulty' => 'required|in:easy,moderate,hard,extreme',
            'duration_days' => 'required|integer|min:1',
            'max_altitude' => 'nullable|integer|min:0',
            'season' => 'nullable|string|max:255',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $route->update($validated);
        return redirect()->route('admin.routes.index')->with('success', 'Route updated.');
    }

    public function destroy(Route $route)
    {
        $route->delete();
        return redirect()->route('admin.routes.index')->with('success', 'Route deleted.');
    }
}