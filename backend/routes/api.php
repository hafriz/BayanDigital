<?php

use App\Http\Controllers\Api\V1\MasjidScreenController;
use App\Http\Controllers\Api\V1\PrayerTimesController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/prayer-times/{zoneCode}', [PrayerTimesController::class, 'show']);

Route::get('/v1/masjids/{publicId}/screen', [MasjidScreenController::class, 'show']);
