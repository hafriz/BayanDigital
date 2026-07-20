<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);

        $masjid->update($validated);

        return redirect()->route('admin.masjids.index')->with('success', 'Masjid settings updated.');
    }
}
