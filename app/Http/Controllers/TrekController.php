<?php

namespace App\Http\Controllers;

use App\Models\Trek;

class TrekController extends Controller
{
    /**
     * Display the specified trek details (public view).
     */
    public function show(Trek $trek)
    {
        // Load agency relationship to show agency name
        $trek->load('agency');
        
        return view('trek.show', compact('trek'));
    }
}