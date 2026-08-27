<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use App\Services\Passport\DigitalTrekPassportService;
use App\Services\Passport\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PassportController extends Controller
{
    /**
     * Display the traveler's digital trek passport.
     */
    public function index(
        DigitalTrekPassportService $passportService,
        AchievementService $achievementService
    ) {
        $user = Auth::user();

        // Get all passport data (statistics, stamps, journeys, etc.)
        $passportData = $passportService->getPassportData($user);

        // Get earned achievements
        $achievements = $achievementService->getEarnedAchievements($user);

        // Calculate XP and Level
        $xp = $passportService->getTotalXP($user);
        $level = $passportService->calculateLevel($user);

        return view('traveler.passport', compact(
            'user',
            'passportData',
            'achievements',
            'xp',
            'level'
        ));
    }

    /**
     * Toggle the privacy of the user's passport.
     */
    public function toggleShare(Request $request)
    {
        $user = Auth::user();
        $newPrivacy = $user->passport_privacy === 'public' ? 'private' : 'public';
        $user->passport_privacy = $newPrivacy;
        $user->save();

        return redirect()->back()->with('success', "Passport is now {$newPrivacy}.");
    }
}