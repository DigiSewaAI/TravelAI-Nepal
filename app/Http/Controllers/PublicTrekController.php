<?php

namespace App\Http\Controllers;

use App\Models\Trek;
use Illuminate\Http\Request;

class PublicTrekController extends Controller
{
    public function index(Request $request)
    {
        // Get category from query string (default: 'trek')
        $category = $request->get('category', 'trek');

        // Start query with agency relation and category filter
        $query = Trek::with('agency')->where('category', $category);

        // Search by trek name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by min/max duration
        if ($request->filled('min_days')) {
            $query->where('duration_days', '>=', $request->min_days);
        }
        if ($request->filled('max_days')) {
            $query->where('duration_days', '<=', $request->max_days);
        }

        // Filter by min/max price
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $treks = $query->latest()->paginate(9)->appends($request->query());

        // Pass both $treks and $category to the view
        return view('public.treks.index', compact('treks', 'category'));
    }
}