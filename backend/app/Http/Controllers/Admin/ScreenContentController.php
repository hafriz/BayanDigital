<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MosqueSetting;
use App\Models\ScreenContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScreenContentController extends Controller
{
    private const TYPES = ['announcement', 'schedule', 'ticker', 'slide', 'image'];

    public function index(Request $request, MosqueSetting $masjid): View
    {
        $this->authorizeMasjid($request, $masjid);
        $type = in_array($request->query('type'), self::TYPES, true) ? $request->query('type') : null;

        return view('admin.contents.index', [
            'masjid' => $masjid,
            'type' => $type,
            'typeCounts' => $masjid->screenContents()
                ->selectRaw('type, count(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type'),
            'contents' => $masjid->screenContents()
                ->when($type, fn ($query) => $query->where('type', $type))
                ->orderBy('sort_order')
                ->latest()
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function create(Request $request, MosqueSetting $masjid): View
    {
        $this->authorizeMasjid($request, $masjid);
        $content = new ScreenContent;
        $content->type = in_array($request->query('type'), self::TYPES, true) ? $request->query('type') : 'announcement';

        return view('admin.contents.form', compact('masjid', 'content'));
    }

    public function store(Request $request, MosqueSetting $masjid): RedirectResponse
    {
        $this->authorizeMasjid($request, $masjid);
        $masjid->screenContents()->create($this->validated($request));

        return redirect()->route('admin.masjids.contents.index', $masjid)->with('success', 'Screen content created.');
    }

    public function edit(Request $request, MosqueSetting $masjid, ScreenContent $content): View
    {
        $this->authorizeMasjid($request, $masjid);
        $this->ensureOwnedBy($masjid, $content);

        return view('admin.contents.form', compact('masjid', 'content'));
    }

    public function update(Request $request, MosqueSetting $masjid, ScreenContent $content): RedirectResponse
    {
        $this->authorizeMasjid($request, $masjid);
        $this->ensureOwnedBy($masjid, $content);
        $content->update($this->validated($request));

        return redirect()->route('admin.masjids.contents.index', $masjid)->with('success', 'Screen content updated.');
    }

    public function destroy(Request $request, MosqueSetting $masjid, ScreenContent $content): RedirectResponse
    {
        $this->authorizeMasjid($request, $masjid);
        $this->ensureOwnedBy($masjid, $content);
        $content->delete();

        return redirect()->route('admin.masjids.contents.index', $masjid)->with('success', 'Screen content deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(self::TYPES)],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:2000'],
            'media_path' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
        ]);
    }

    private function ensureOwnedBy(MosqueSetting $masjid, ScreenContent $content): void
    {
        abort_unless($content->mosque_setting_id === $masjid->id, 404);
    }

    private function authorizeMasjid(Request $request, MosqueSetting $masjid): void
    {
        abort_unless($request->user()->isAdmin() || $request->user()->mosque_setting_id === $masjid->id, 403);
    }
}
