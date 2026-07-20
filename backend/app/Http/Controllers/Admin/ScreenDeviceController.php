<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use App\Models\ScreenDevice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ScreenDeviceController extends Controller
{
    public function index(MosqueSetting $masjid): View
    {
        return view('admin.devices.index', [
            'masjid' => $masjid,
            'devices' => $masjid->screenDevices()
                ->with('approver')
                ->orderByRaw("status = 'pending' desc")
                ->latest()
                ->paginate(20),
        ]);
    }

    public function approve(Request $request, MosqueSetting $masjid, ScreenDevice $device): RedirectResponse
    {
        $this->ensureOwnedBy($masjid, $device);

        if ($device->status !== 'pending' || $device->expires_at->isPast()) {
            return back()->withErrors(['device' => 'This pairing request is no longer available for approval.']);
        }

        $device->approve($request->user());

        return back()->with('success', 'TV device approved. It can now connect securely.');
    }

    public function reject(MosqueSetting $masjid, ScreenDevice $device): RedirectResponse
    {
        $this->ensureOwnedBy($masjid, $device);
        $device->update(['status' => 'rejected']);

        return back()->with('success', 'Pairing request rejected.');
    }

    public function revoke(MosqueSetting $masjid, ScreenDevice $device): RedirectResponse
    {
        $this->ensureOwnedBy($masjid, $device);
        $device->forceFill([
            'status' => 'revoked',
            'device_token' => null,
            'token_hash' => null,
        ])->save();

        return back()->with('success', 'TV access revoked immediately.');
    }

    private function ensureOwnedBy(MosqueSetting $masjid, ScreenDevice $device): void
    {
        abort_unless($device->mosque_setting_id === $masjid->id, 404);
    }
}
