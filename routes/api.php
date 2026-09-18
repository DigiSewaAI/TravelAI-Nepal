<?php

use App\Http\Controllers\Api\SosController;
use App\Http\Controllers\Api\ItineraryController;
use App\Http\Controllers\Api\QuotationRequestController;
use App\Http\Controllers\Api\MapDataController;

// FIX-11: AI endpoint — 10/min (user or IP)
Route::post('/itinerary/generate', [ItineraryController::class, 'generate'])->middleware('throttle:ai');

// FIX-11: SOS — 3/min (IP)
Route::post('/sos', [SosController::class, 'store'])->middleware('throttle:sos');

// ✅ Public: Provider list (no auth) — FIX-11: 30/min
Route::get('/providers/list', [QuotationRequestController::class, 'providersList'])->middleware('throttle:api');

// ✅ Public: Send quotation request (guest + registered users both allowed) — FIX-11: 10/min
Route::post('/quotation-request', [QuotationRequestController::class, 'store'])->middleware('throttle:ai');
// GLOBE-01: Public map data — 30/min via throttle:api
Route::get('/map/init', [MapDataController::class, 'init'])
    ->middleware('throttle:api')
    ->name('api.map.init');
// GLOBE-06: Public single route geometry — 30/min via throttle:api
Route::get('/map/route/{slug}', [MapDataController::class, 'route'])
    ->middleware('throttle:api')
    ->name('api.map.route');
