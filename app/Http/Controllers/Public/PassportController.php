<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Passport\DigitalTrekPassportService;
use App\Services\Passport\AchievementService;
use Illuminate\Http\Request;

class PassportController extends Controller
{
    /**
     * Display public passport for a user by public_id.
     */
    public function show(
        string $publicId,
        DigitalTrekPassportService $passportService,
        AchievementService $achievementService
    ) {
        // Find user by public_id
        $user = User::where('passport_public_id', $publicId)->firstOrFail();

        // Check privacy setting
        if ($user->passport_privacy === 'private') {
            abort(404, 'This passport is private.');
        }

        // Get limited public data (no email, phone, or private info)
        $profile = [
            'name' => $user->name,
            'avatar' => $user->avatar,
            'member_since' => $user->created_at,
        ];

        // ✅ Statistics (unchanged)
        $statistics = $passportService->getStatistics($user);

        // ✅ Stamps - Fixed object access
        $stamps = $passportService->getStamps($user)->map(function ($stamp) {
            return [
                'location' => $stamp->location ?? $stamp['location'] ?? 'Unknown Location',
                'date' => $stamp->date ? $stamp->date->format('M d, Y') : now()->format('M d, Y'),
                'altitude' => $stamp->altitude ?? null,
                'type' => $stamp->type ?? null,
            ];
        });

        // ✅ Achievements (unchanged - assuming it works)
        $achievements = $achievementService->getEarnedAchievements($user)->map(function ($item) {
            return [
                'name' => $item->achievement->name,
                'icon' => $item->achievement->icon,
                'earned_at' => $item->earned_at->format('M d, Y'),
                'rarity' => $item->achievement->rarity,
            ];
        });

        $level = $passportService->calculateLevel($user);
        $xp = $passportService->getTotalXP($user);

        return view('public.passport', compact(
            'user',
            'profile',
            'statistics',
            'stamps',
            'achievements',
            'level',
            'xp'
        ));
    }
}