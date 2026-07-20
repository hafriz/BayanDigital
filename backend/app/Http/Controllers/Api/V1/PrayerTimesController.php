<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use App\Models\ScreenContent;
use App\Services\JakimPrayerTimeService;
use Illuminate\Http\JsonResponse;

class PrayerTimesController extends Controller
{
    public function show(string $zoneCode, JakimPrayerTimeService $service): JsonResponse
    {
        $settings = MosqueSetting::query()->where('zone_code', strtoupper($zoneCode))->firstOrFail();
        $today = $service->today($settings->zone_code, $settings->prayer_offsets ?? []);

        return response()->json([
            'mosque' => [
                'name' => $settings->name,
                'zone_code' => $settings->zone_code,
                'iqamah_minutes' => $settings->iqamah_minutes ?? [],
                'silent_mode_minutes' => $settings->silent_mode_minutes,
            ],
            'date' => [
                'gregorian' => $today->prayer_date->toDateString(),
                'hijri' => $today->hijri_date,
            ],
            'timeline' => $today->times,
            'announcements' => ScreenContent::currentlyActive()
                ->orderBy('sort_order')
                ->get(['type', 'title', 'body', 'media_path']),
            'synced_at' => now()->toIso8601String(),
        ]);
    }
}
