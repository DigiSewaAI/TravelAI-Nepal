<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function bookings(Request $request)
    {
        $startDate = $request->start_date ?? now()->subDays(30);
        $endDate = $request->end_date ?? now();

        $bookings = Booking::with(['service', 'traveler'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $total = $bookings->count();
        $confirmed = $bookings->where('status', 'confirmed')->count();
        $completed = $bookings->where('status', 'completed')->count();
        $cancelled = $bookings->where('status', 'cancelled')->count();

        return view('admin.reports.bookings', compact('bookings', 'total', 'confirmed', 'completed', 'cancelled', 'startDate', 'endDate'));
    }

    public function payments(Request $request)
    {
        $startDate = $request->start_date ?? now()->subDays(30);
        $endDate = $request->end_date ?? now();

        $payments = Payment::with(['provider'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'success')
            ->get();

        $totalAmount = $payments->sum('amount');
        $totalCount = $payments->count();

        return view('admin.reports.payments', compact('payments', 'totalAmount', 'totalCount', 'startDate', 'endDate'));
    }

    public function providers()
    {
        $total = Provider::count();
        $verified = Provider::where('verification_status', 'verified')->count();
        $pending = Provider::where('verification_status', 'pending')->count();
        $rejected = Provider::where('verification_status', 'rejected')->count();

        $providers = Provider::with(['user', 'types'])->latest()->paginate(20);

        return view('admin.reports.providers', compact('providers', 'total', 'verified', 'pending', 'rejected'));
    }
}