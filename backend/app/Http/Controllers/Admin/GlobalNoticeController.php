<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScreenContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GlobalNoticeController extends Controller
{
    public function index(): View
    {
        return view('admin.global-notices.index', [
            'notices' => ScreenContent::query()
                ->whereNull('mosque_setting_id')
                ->where('type', 'global_notice')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.global-notices.form', ['notice' => new ScreenContent(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        ScreenContent::query()->create($this->validated($request));

        return redirect()->route('admin.global-notices.index')->with('success', 'Global notice published to all screens and public pages.');
    }

    public function edit(ScreenContent $globalNotice): View
    {
        $this->ensureGlobalNotice($globalNotice);

        return view('admin.global-notices.form', ['notice' => $globalNotice]);
    }

    public function update(Request $request, ScreenContent $globalNotice): RedirectResponse
    {
        $this->ensureGlobalNotice($globalNotice);
        $globalNotice->update($this->validated($request));

        return redirect()->route('admin.global-notices.index')->with('success', 'Global notice updated.');
    }

    public function destroy(ScreenContent $globalNotice): RedirectResponse
    {
        $this->ensureGlobalNotice($globalNotice);
        $globalNotice->delete();

        return redirect()->route('admin.global-notices.index')->with('success', 'Global notice removed from all screens and public pages.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:2000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['required', 'boolean'],
        ]);

        return ['type' => 'global_notice', 'sort_order' => 0, ...$data];
    }

    private function ensureGlobalNotice(ScreenContent $notice): void
    {
        abort_unless($notice->mosque_setting_id === null && $notice->type === 'global_notice', 404);
    }
}
