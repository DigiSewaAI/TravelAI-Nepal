<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class LogController extends Controller
{
    public function index()
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = file_exists($logFile) ? file_get_contents($logFile) : 'No logs found.';
        
        // Limit to last 500KB to prevent browser crash if log is huge
        if (strlen($logs) > 500000) {
            $logs = "... [Truncated] ...\n" . substr($logs, -500000);
        }

        return view('admin.logs.index', compact('logs'));
    }

    public function clear()
    {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }
        return back()->with('success', 'Log file cleared successfully.');
    }
}