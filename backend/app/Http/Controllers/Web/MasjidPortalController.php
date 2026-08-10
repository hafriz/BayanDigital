<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use App\Models\ScreenContent;
use App\Services\JakimPrayerTimeService;
use App\Services\LogoUrlResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class MasjidPortalController extends Controller
{
    public function __invoke(string $slug, JakimPrayerTimeService $prayerTimes, LogoUrlResolver $logos): View
    {
        $masjid = MosqueSetting::query()
            ->where('public_slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();

        $content = $masjid->screenContents()->currentlyActive()->orderBy('sort_order')->get();

        return view('masjids.portal', [
            'masjid' => $masjid,
            'logoUrl' => $logos->resolve($masjid->logo_url),
            'defaultLogoUrl' => $logos->defaultUrl(),
            'prayerTime' => $prayerTimes->today($masjid->zone_code, $masjid->prayer_offsets ?? []),
            'announcements' => $content->where('type', 'announcement'),
            'schedules' => $content->where('type', 'schedule')->map(function (ScreenContent $schedule): ScreenContent {
                if ($schedule->media_path && str_starts_with($schedule->media_path, 'masjids/')) {
                    $schedule->media_path = Storage::disk('public')->url($schedule->media_path);
                }

                return $schedule;
            }),
            'globalNotices' => ScreenContent::currentlyActive()
                ->whereNull('mosque_setting_id')
                ->where('type', 'global_notice')
                ->latest()
                ->get(),
            'committeeMembers' => $masjid->committeeMembers()->where('is_active', true)->orderBy('display_order')->orderBy('id')->get(),
            'donationQrUrl' => $masjid->donation_qr_image
                ? Storage::disk('public')->url($masjid->donation_qr_image)
                : $masjid->donation_qr_url,
        ]);
    }
}
