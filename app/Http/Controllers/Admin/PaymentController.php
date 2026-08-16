<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of all payments.
     */
    public function index()
    {
        $payments = Payment::with(['provider', 'user', 'payable'])
            ->latest()
            ->paginate(20);

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Display the specified payment details.
     * Loads relationships for provider, user, and payable (polymorphic).
     */
    public function show(Payment $payment)
    {
        // Eager load relationships for the view
        $payment->load(['provider', 'user', 'payable']);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Refund a payment (mark as refunded).
     */
    public function refund(Payment $payment)
    {
        // Only allow refund if status is 'success'
        if ($payment->status !== 'success') {
            return back()->with('error', 'Only successful payments can be refunded.');
        }

        $payment->status = 'refunded';
        $payment->save();

        return back()->with('success', 'Payment refunded successfully.');
    }

    /**
     * Delete a payment record.
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment deleted successfully.');
    }
}