<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MasjidController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status');

        $masjids = MosqueSetting::query()
            ->when(! $request->user()->isAdmin(), fn ($query) => $query->whereKey($request->user()->mosque_setting_id))
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

    public function edit(Request $request, MosqueSetting $masjid): View
    {
        $this->authorizeMasjid($request, $masjid);

        return view('admin.masjids.edit', [
            'masjid' => $masjid,
            'jakimZones' => config('jakim.zones', []),
        ]);
    }

    public function update(Request $request, MosqueSetting $masjid): RedirectResponse
    {
        $this->authorizeMasjid($request, $masjid);
        $wasApproved = $masjid->status === 'approved';
        $zoneCodes = collect(config('jakim.zones', []))->flatMap(fn (array $zones) => array_keys($zones))->all();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'public_slug' => ['required', 'alpha_dash', 'max:150', Rule::unique('mosque_settings')->ignore($masjid)],
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
            'donation_qr_url' => ['nullable', 'url:http,https', 'max:255'],
            'committee' => ['nullable', 'string', 'max:3000'],
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

        $validated['public_slug'] = Str::lower($validated['public_slug']);
        $validated['committee'] = collect(preg_split('/\r\n|\r|\n/', $validated['committee'] ?? ''))
            ->map(fn (string $line) => trim($line))->filter()->values()->all();
        $masjid->update($validated);

        $message = 'Masjid settings updated.';
        if (! $wasApproved && $masjid->status === 'approved' && $masjid->contact_email) {
            $operator = User::query()->firstOrCreate(['email' => $masjid->contact_email], [
                'name' => $masjid->contact_name ?: $masjid->name.' Operator',
                'password' => Str::password(32),
                'role' => User::ROLE_OPERATOR,
                'is_active' => true,
            ]);

            if (! $operator->isAdmin()) {
                $operator->update(['mosque_setting_id' => $masjid->id]);
                $message .= $operator->wasRecentlyCreated
                    ? ' An operator invitation account was created for '.$operator->email.'.'
                    : ' The existing operator was assigned to this masjid.';
            }
        }

        return redirect()->route('admin.masjids.index')->with('success', $message);
    }

    private function authorizeMasjid(Request $request, MosqueSetting $masjid): void
    {
        abort_unless($request->user()->isAdmin() || $request->user()->mosque_setting_id === $masjid->id, 403);
    }
}
