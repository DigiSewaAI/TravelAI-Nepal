<?php

use App\Http\Controllers\Api\SosController;
use App\Http\Controllers\Api\ItineraryController;
use App\Http\Controllers\Api\QuotationRequestController;

Route::post('/itinerary/generate', [ItineraryController::class, 'generate']);
Route::post('/sos', [SosController::class, 'store']);

// ✅ Public: Provider list (no auth)
Route::get('/providers/list', [QuotationRequestController::class, 'providersList']);

// ✅ Public: Send quotation request (guest + registered users both allowed)
Route::post('/quotation-request', [QuotationRequestController::class, 'store']);