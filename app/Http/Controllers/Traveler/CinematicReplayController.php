<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Services\JourneyReplay\CinematicReplayService;
use Illuminate\Support\Facades\Auth;

class CinematicReplayController extends Controller
{
    protected $cinematicService;

    public function __construct(CinematicReplayService $cinematicService)
    {
        $this->cinematicService = $cinematicService;
    }

    public function index()
    {
        $user = Auth::user();
        $data = $this->cinematicService->getCinematicData($user);
        return view('traveler.cinematic-replay', compact('data'));
    }
}