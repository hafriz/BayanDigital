<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use App\Services\JakimPrayerTimeService;
use Illuminate\Contracts\View\View;

class MasjidPortalController extends Controller
{
    public function __invoke(string $slug, JakimPrayerTimeService $prayerTimes): View
    {
        $masjid = MosqueSetting::query()
            ->where('public_slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();

        return view('masjids.portal', [
            'masjid' => $masjid,
            'prayerTime' => $prayerTimes->today($masjid->zone_code, $masjid->prayer_offsets ?? []),
            'announcements' => $masjid->screenContents()->currentlyActive()->where('type', 'announcement')->orderBy('sort_order')->get(),
        ]);
    }
}
