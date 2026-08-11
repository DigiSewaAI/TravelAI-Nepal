<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['provider', 'user', 'payable'])
            ->latest()
            ->paginate(20);
        return view('admin.payments.index', compact('payments'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['provider', 'user', 'payable']);
        return view('admin.payments.show', compact('payment'));
    }

    public function refund(Payment $payment)
    {
        $payment->status = 'refunded';
        $payment->save();
        return back()->with('success', 'Payment refunded successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return redirect()->route('admin.payments.index')->with('success', 'Payment deleted.');
    }
}