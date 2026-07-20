<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MasjidRegistrationController extends Controller
{
    public function create(): View
    {
        return view('masjids.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(['masjid', 'surau'])],
            'zone_code' => ['required', 'string', 'max:10'],
            'contact_name' => ['required', 'string', 'max:120'],
            'contact_phone' => ['required', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $masjid = MosqueSetting::create([
            ...$validated,
            'zone_code' => strtoupper($validated['zone_code']),
            'public_id' => $this->newPublicId(),
            'status' => 'pending',
            'prayer_offsets' => [],
            'iqamah_minutes' => [
                'subuh' => 10,
                'zohor' => 10,
                'asar' => 10,
                'maghrib' => 5,
                'isyak' => 10,
            ],
        ]);

        return redirect()->route('masjids.registered', $masjid->public_id);
    }

    public function registered(string $publicId): View
    {
        return view('masjids.registered', [
            'masjid' => MosqueSetting::query()->where('public_id', $publicId)->firstOrFail(),
        ]);
    }

    private function newPublicId(): string
    {
        do {
            $id = 'MSJ-' . Str::upper(Str::random(8));
        } while (MosqueSetting::query()->where('public_id', $id)->exists());

        return $id;
    }
}
