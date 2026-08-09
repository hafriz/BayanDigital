<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MasjidController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');

        $masjids = MosqueSetting::query()
            ->withCount('screenContents')
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('public_id', 'like', "%{$search}%")
                ->orWhere('contact_name', 'like', "%{$search}%")))
            ->when(in_array($status, ['pending', 'approved', 'suspended', 'rejected'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.masjids.index', compact('masjids', 'search', 'status'));
    }

    public function edit(MosqueSetting $masjid): View
    {
        return view('admin.masjids.edit', [
            'masjid' => $masjid,
            'jakimZones' => config('jakim.zones', []),
        ]);
    }

    public function update(Request $request, MosqueSetting $masjid): RedirectResponse
    {
        $zoneCodes = collect(config('jakim.zones', []))->flatMap(fn (array $zones) => array_keys($zones))->all();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(['masjid', 'surau'])],
            'status' => ['required', Rule::in(['pending', 'approved', 'suspended', 'rejected'])],
            'zone_code' => ['required', Rule::in($zoneCodes)],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'silent_mode_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'screen_theme' => ['required', Rule::in(['emerald', 'midnight', 'sand', 'royal'])],
            'time_format' => ['required', Rule::in(['24h', '12h'])],
            'logo_url' => ['nullable', 'url:http,https', 'max:255'],
            'donation_qr_image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'donation_caption' => ['nullable', 'string', 'max:200'],
            'donation_account' => ['nullable', 'string', 'max:150'],
            'remove_donation_qr' => ['nullable', 'boolean'],
            'google_calendar_ics_url' => [
                'nullable',
                'url:http,https',
                'max:1000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));
                    if ($value && ! in_array($host, ['calendar.google.com', 'www.google.com'], true)) {
                        $fail('Use the Public address in iCal format from Google Calendar.');
                    }
                },
            ],
            'screen_sleep_enabled' => ['required', 'boolean'],
            'screen_sleep_time' => ['required', 'date_format:H:i'],
            'screen_wake_mode' => ['required', Rule::in(['fixed', 'before_subuh'])],
            'screen_wake_time' => ['required', 'date_format:H:i'],
            'wake_before_subuh_minutes' => ['required', 'integer', 'min:0', 'max:180'],
        ]);

        $oldImage = $masjid->donation_qr_image;
        $newImage = null;

        if ($request->hasFile('donation_qr_image')) {
            $file = $request->file('donation_qr_image');
            $newImage = $file->storeAs(
                'donation-qr',
                Str::uuid().'.'.$file->extension(),
                'public'
            );
            $validated['donation_qr_image'] = $newImage;
        } elseif ($request->boolean('remove_donation_qr')) {
            $validated['donation_qr_image'] = null;
        }

        unset($validated['remove_donation_qr']);

        try {
            $masjid->update($validated);
        } catch (\Throwable $exception) {
            if ($newImage) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if ($oldImage && $oldImage !== $masjid->donation_qr_image) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('admin.masjids.index')->with('success', 'Masjid settings updated.');
    }
}
