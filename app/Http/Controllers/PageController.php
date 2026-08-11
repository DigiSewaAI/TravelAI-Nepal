<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Trek;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function features()
    {
        return view('pages.features');
    }

    public function howItWorks()
    {
        return view('pages.how-it-works');
    }

    public function agencies(Request $request)
    {
        $query = Agency::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $agencies = $query->paginate(12)->appends($request->query());

        return view('pages.agencies', compact('agencies'));
    }
    public function pricing()
{
    $plans = \App\Models\Plan::all();
    return view('public.pricing', compact('plans'));
}
}