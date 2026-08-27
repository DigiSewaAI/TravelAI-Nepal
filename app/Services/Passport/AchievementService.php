<?php

namespace App\Services\Passport;

use App\Models\User;
use App\Models\Achievement;
use App\Models\UserAchievement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    /**
     * Evaluate and unlock achievements for a user
     */
    public function evaluateAndUnlock(User $user): array
    {
        $unlocked = [];
        $achievements = Achievement::where('is_active', true)->get();

        // Get user stats once
        $stats = app(DigitalTrekPassportService::class)->getStatistics($user);
        $scans = $this->getUserScans($user);

        foreach ($achievements as $achievement) {
            // Skip if already earned
            if ($this->hasAchievement($user, $achievement)) {
                continue;
            }

            if ($this->checkCriteria($achievement, $stats, $scans)) {
                $this->unlockAchievement($user, $achievement);
                $unlocked[] = $achievement;
            }
        }

        return $unlocked;
    }

    /**
     * Check if user meets achievement criteria
     */
    private function checkCriteria(Achievement $achievement, array $stats, Collection $scans): bool
    {
        return match($achievement->slug) {
            'first_checkin' => $stats['total_checkins'] >= 1,
            'first_trek' => $stats['total_treks'] >= 1,
            'stamp_collector_5' => $stats['unique_waypoints'] >= 5,
            'stamp_collector_10' => $stats['unique_waypoints'] >= 10,
            'stamp_collector_25' => $stats['unique_waypoints'] >= 25,
            'stamp_collector_50' => $stats['unique_waypoints'] >= 50,
            'altitude_3000' => $stats['highest_altitude'] >= 3000,
            'altitude_4000' => $stats['highest_altitude'] >= 4000,
            'altitude_5000' => $stats['highest_altitude'] >= 5000,
            'altitude_6000' => $stats['highest_altitude'] >= 6000,
            'trek_completed_3' => $stats['completed_treks'] >= 3,
            'trek_completed_5' => $stats['completed_treks'] >= 5,
            'trek_completed_10' => $stats['completed_treks'] >= 10,
            'everest_base_camp' => $this->checkDestination($scans, ['everest base camp', 'ebc', 'everest']),
            'annapurna_circuit' => $this->checkDestination($scans, ['annapurna', 'thorong la', 'muktinath']),
            'langtang' => $this->checkDestination($scans, ['langtang', 'kyangjin']),
            'manaslu' => $this->checkDestination($scans, ['manaslu', 'larkya la']),
            'kanchenjunga' => $this->checkDestination($scans, ['kanchenjunga']),
            'mardi_himal' => $this->checkDestination($scans, ['mardi himal']),
            'ghorepani_poon_hill' => $this->checkDestination($scans, ['poon hill', 'ghorepani']),
            'upper_mustang' => $this->checkDestination($scans, ['lo manthang', 'mustang']),
            default => false,
        };
    }

    /**
     * Check if user has visited any waypoint matching the destination keywords
     */
    private function checkDestination(Collection $scans, array $keywords): bool
    {
        foreach ($scans as $scan) {
            $name = strtolower($scan->waypoint->name ?? $scan->checkpoint_name);
            foreach ($keywords as $keyword) {
                if (str_contains($name, strtolower($keyword))) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Unlock achievement for user
     */
    private function unlockAchievement(User $user, Achievement $achievement): void
    {
        UserAchievement::create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'earned_at' => now(),
            'metadata' => [
                'unlocked_via' => 'auto_evaluation',
                'unlocked_at' => now()->toISOString(),
            ],
        ]);

        Log::info("🎖️ Achievement unlocked", [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'achievement' => $achievement->slug,
            'points' => $achievement->points,
        ]);
    }

    /**
     * Check if user already has achievement
     */
    public function hasAchievement(User $user, Achievement $achievement): bool
    {
        return UserAchievement::where('user_id', $user->id)
                              ->where('achievement_id', $achievement->id)
                              ->exists();
    }

    /**
     * Get earned achievements for user
     */
    public function getEarnedAchievements(User $user): Collection
    {
        return UserAchievement::with('achievement')
                              ->where('user_id', $user->id)
                              ->orderBy('earned_at', 'desc')
                              ->get();
    }

    /**
     * Get total XP from achievements
     */
    public function getTotalXP(User $user): int
    {
        return UserAchievement::where('user_id', $user->id)
                              ->with('achievement')
                              ->get()
                              ->sum(fn($ua) => $ua->achievement->points ?? 0);
    }

    /**
     * Get user's scans with waypoint data
     */
    private function getUserScans(User $user): Collection
    {
        return QrScan::whereHas('booking', function ($q) use ($user) {
            $q->where('traveler_id', $user->id);
        })
        ->with('waypoint')
        ->get();
    }
}