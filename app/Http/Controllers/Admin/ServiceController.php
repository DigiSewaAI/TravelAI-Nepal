<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Provider;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with(['provider', 'category'])->latest()->paginate(20);
        return view('admin.services.index', compact('services'));
    }

    public function show(Service $service)
    {
        $service->load(['provider', 'category', 'trekDetail', 'tourDetail', 'hotelDetail']);
        return view('admin.services.show', compact('service'));
    }

    public function toggleStatus(Service $service)
    {
        $service->status = $service->status === 'active' ? 'inactive' : 'active';
        $service->save();
        return back()->with('success', 'Service status updated.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }
}