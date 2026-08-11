<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function stripe(Request $request)
    {
        $payload = $request->all();
        $sigHeader = $request->header('Stripe-Signature');

        // Verify webhook signature (optional but recommended)
        // You can add signature verification here

        $result = $this->paymentService->handleWebhook($payload);

        return response()->json(['received' => true], 200);
    }
}