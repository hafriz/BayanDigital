<?php

use App\Http\Controllers\Api\V1\MasjidScreenController;
use App\Http\Controllers\Api\V1\PrayerTimesController;
use App\Http\Controllers\Api\V1\ScreenPairingController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/prayer-times/{zoneCode}', [PrayerTimesController::class, 'show']);

Route::get('/v1/masjids/search', [ScreenPairingController::class, 'search'])->middleware('throttle:30,1');
Route::post('/v1/masjids/{publicId}/devices/pair', [ScreenPairingController::class, 'requestPairing'])->middleware('throttle:10,1');
Route::get('/v1/pairing/{requestId}', [ScreenPairingController::class, 'status'])->middleware('throttle:60,1');
Route::get('/v1/masjids/{publicId}/screen', [MasjidScreenController::class, 'show'])->middleware('throttle:120,1');
