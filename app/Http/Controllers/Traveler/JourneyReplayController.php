<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Services\JourneyReplay\JourneyReplayService;
use Illuminate\Support\Facades\Auth;

class JourneyReplayController extends Controller
{
    protected $replayService;

    public function __construct(JourneyReplayService $replayService)
    {
        $this->replayService = $replayService;
    }

    public function index()
    {
        $user = Auth::user();
        $replayData = $this->replayService->getReplay($user);

        return view('traveler.journey-replay', compact('replayData'));
    }
}