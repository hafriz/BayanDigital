<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use Illuminate\Contracts\View\View;

class MasjidPortalController extends Controller
{
    public function __invoke(string $publicId): View
    {
        $masjid = MosqueSetting::query()
            ->where('public_id', $publicId)
            ->where('status', 'approved')
            ->firstOrFail();

        $committeeMembers = $masjid->committeeMembers()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return view('masjids.portal', compact('masjid', 'committeeMembers'));
    }
}
