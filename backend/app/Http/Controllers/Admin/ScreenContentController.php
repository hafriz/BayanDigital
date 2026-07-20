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
    public function index(MosqueSetting $masjid): View
    {
        return view('admin.contents.index', [
            'masjid' => $masjid,
            'contents' => $masjid->screenContents()->orderBy('sort_order')->latest()->paginate(20),
        ]);
    }

    public function create(MosqueSetting $masjid): View
    {
        return view('admin.contents.form', ['masjid' => $masjid, 'content' => new ScreenContent]);
    }

    public function store(Request $request, MosqueSetting $masjid): RedirectResponse
    {
        $masjid->screenContents()->create($this->validated($request));

        return redirect()->route('admin.masjids.contents.index', $masjid)->with('success', 'Screen content created.');
    }

    public function edit(MosqueSetting $masjid, ScreenContent $content): View
    {
        $this->ensureOwnedBy($masjid, $content);

        return view('admin.contents.form', compact('masjid', 'content'));
    }

    public function update(Request $request, MosqueSetting $masjid, ScreenContent $content): RedirectResponse
    {
        $this->ensureOwnedBy($masjid, $content);
        $content->update($this->validated($request));

        return redirect()->route('admin.masjids.contents.index', $masjid)->with('success', 'Screen content updated.');
    }

    public function destroy(MosqueSetting $masjid, ScreenContent $content): RedirectResponse
    {
        $this->ensureOwnedBy($masjid, $content);
        $content->delete();

        return redirect()->route('admin.masjids.contents.index', $masjid)->with('success', 'Screen content deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(['announcement', 'ticker', 'slide', 'image'])],
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
}
