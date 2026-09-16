<?php

use App\Http\Controllers\Api\SosController;
use App\Http\Controllers\Api\ItineraryController;
use App\Http\Controllers\Api\QuotationRequestController;

// FIX-11: AI endpoint — 10/min (user or IP)
Route::post('/itinerary/generate', [ItineraryController::class, 'generate'])->middleware('throttle:ai');

// FIX-11: SOS — 3/min (IP)
Route::post('/sos', [SosController::class, 'store'])->middleware('throttle:sos');

// ✅ Public: Provider list (no auth) — FIX-11: 30/min
Route::get('/providers/list', [QuotationRequestController::class, 'providersList'])->middleware('throttle:api');

// ✅ Public: Send quotation request (guest + registered users both allowed) — FIX-11: 10/min
Route::post('/quotation-request', [QuotationRequestController::class, 'store'])->middleware('throttle:ai');