<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use App\Services\JakimPrayerTimeService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class MasjidPortalController extends Controller
{
    public function __invoke(string $publicId, JakimPrayerTimeService $service): View
    {
        $masjid = MosqueSetting::query()
            ->where('public_id', strtoupper($publicId))
            ->where('status', 'approved')
            ->firstOrFail();

        return view('masjids.show', [
            'masjid' => $masjid,
            'prayerTime' => $service->today($masjid->zone_code, $masjid->prayer_offsets ?? []),
            'donationQrUrl' => $masjid->donation_qr_image
                ? Storage::disk('public')->url($masjid->donation_qr_image)
                : null,
        ]);
    }
}
