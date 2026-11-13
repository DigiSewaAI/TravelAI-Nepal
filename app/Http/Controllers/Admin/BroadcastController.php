<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class BroadcastController extends Controller
{
    public function index()
    {
        return view('admin.broadcast.index');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'audience' => 'required|in:all,traveler,provider_owner,staff',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:5000',
        ]);

        $query = User::query();

        if ($validated['audience'] !== 'all') {
            $query->where('role', $validated['audience']);
        }

        $count = 0;
        
        // Chunking is used to prevent memory exhaustion and Timeout limits
        $query->chunkById(100, function ($users) use ($validated, &$count) {
            Notification::send($users, new AdminBroadcastNotification($validated['subject'], $validated['message']));
            $count += $users->count();
        });

        return back()->with('success', "Announcement sent successfully to {$count} users.");
    }
}