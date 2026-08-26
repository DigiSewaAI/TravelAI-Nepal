<?php

use App\Http\Controllers\Api\SosController;
use App\Http\Controllers\Api\ItineraryController;

Route::post('/itinerary/generate', [ItineraryController::class, 'generate']);
Route::post('/sos', [SosController::class, 'store']);
