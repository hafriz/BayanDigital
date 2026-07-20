<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use App\Models\ScreenContent;
use App\Services\JakimPrayerTimeService;
use Illuminate\Http\JsonResponse;

class MasjidScreenController extends Controller
{
    public function show(string $publicId, JakimPrayerTimeService $service): JsonResponse
    {
        $settings = MosqueSetting::query()
            ->where('public_id', strtoupper($publicId))
            ->first();

        if (! $settings) {
            return response()->json([
                'message' => 'This masjid or surau ID was not found.',
                'code' => 'MASJID_NOT_FOUND',
            ], 404);
        }

        if ($settings->status !== 'approved') {
            return response()->json([
                'message' => match ($settings->status) {
                    'pending' => 'This registration is waiting for administrator approval.',
                    'suspended' => 'This display has been suspended. Please contact the administrator.',
                    'rejected' => 'This registration was not approved. Please contact the administrator.',
                    default => 'This display is not currently active.',
                },
                'code' => 'MASJID_'.strtoupper($settings->status),
            ], 403);
        }

        $today = $service->today($settings->zone_code, $settings->prayer_offsets ?? []);

        return response()->json([
            'masjid' => [
                'id' => $settings->public_id,
                'type' => $settings->type,
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
                ->where(fn ($query) => $query->whereNull('mosque_setting_id')->orWhere('mosque_setting_id', $settings->id))
                ->orderBy('sort_order')
                ->get(['type', 'title', 'body', 'media_path']),
            'android' => config('android'),
            'synced_at' => now()->toIso8601String(),
        ]);
    }
}
