<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use App\Models\ScreenDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScreenPairingController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);
        $query = trim($validated['q']);

        $masjids = MosqueSetting::query()
            ->where('status', 'approved')
            ->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$query}%")
                ->orWhere('public_id', 'like', "%{$query}%"))
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn (MosqueSetting $masjid) => [
                'id' => $masjid->public_id,
                'name' => $masjid->name,
                'type' => $masjid->type,
                'zone_code' => $masjid->zone_code,
            ]);

        return response()->json(['results' => $masjids]);
    }

    public function requestPairing(Request $request, string $publicId): JsonResponse
    {
        $validated = $request->validate([
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);
        $masjid = MosqueSetting::query()
            ->where('public_id', strtoupper($publicId))
            ->where('status', 'approved')
            ->first();

        if (! $masjid) {
            return response()->json(['message' => 'This masjid or surau is not available for pairing.'], 404);
        }

        $device = $masjid->screenDevices()->create([
            'request_id' => (string) Str::uuid(),
            'pairing_code' => (string) random_int(100000, 999999),
            'device_name' => ($validated['device_name'] ?? null) ?: 'Android TV',
            'status' => 'pending',
            'expires_at' => now()->addMinutes(15),
        ]);

        return response()->json([
            'request_id' => $device->request_id,
            'pairing_code' => $device->pairing_code,
            'masjid_id' => $masjid->public_id,
            'masjid_name' => $masjid->name,
            'status' => 'pending',
            'expires_at' => $device->expires_at->toIso8601String(),
        ], 201);
    }

    public function status(Request $request, string $requestId): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);
        $device = ScreenDevice::query()->with('mosqueSetting')->where('request_id', $requestId)->first();

        if (! $device || ! hash_equals($device->pairing_code, $validated['code'])) {
            return response()->json(['message' => 'Pairing request not found.'], 404);
        }

        if ($device->expires_at->isPast() && in_array($device->status, ['pending', 'approved'], true)) {
            $device->update(['status' => 'expired']);
        }

        if ($device->status === 'approved') {
            return response()->json([
                'status' => 'approved',
                'masjid_id' => $device->mosqueSetting->public_id,
                'masjid_name' => $device->mosqueSetting->name,
                'device_token' => $device->device_token,
            ]);
        }

        return response()->json([
            'status' => $device->status,
            'message' => match ($device->status) {
                'pending' => 'Waiting for administrator approval.',
                'rejected' => 'The administrator rejected this pairing request.',
                'revoked' => 'This TV access has been revoked.',
                'expired' => 'This pairing request expired. Start a new request.',
                default => 'This pairing request is not active.',
            },
        ]);
    }
}
