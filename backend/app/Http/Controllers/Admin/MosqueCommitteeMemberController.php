<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MosqueCommitteeMember;
use App\Models\MosqueSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MosqueCommitteeMemberController extends Controller
{
    public function index(MosqueSetting $masjid): View
    {
        return view('admin.committee-members.index', [
            'masjid' => $masjid,
            'members' => $masjid->committeeMembers()->orderBy('display_order')->orderBy('id')->get(),
        ]);
    }

    public function create(MosqueSetting $masjid): View
    {
        return view('admin.committee-members.form', [
            'masjid' => $masjid,
            'member' => new MosqueCommitteeMember(['is_active' => true]),
        ]);
    }

    public function store(Request $request, MosqueSetting $masjid): RedirectResponse
    {
        $data = $this->validated($request);
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('committee-members', 'public');
        }
        $masjid->committeeMembers()->create($data);

        return redirect()->route('admin.masjids.committee-members.index', $masjid)->with('success', 'Committee member added.');
    }

    public function edit(MosqueSetting $masjid, MosqueCommitteeMember $committeeMember): View
    {
        $this->ensureOwnedBy($masjid, $committeeMember);

        return view('admin.committee-members.form', ['masjid' => $masjid, 'member' => $committeeMember]);
    }

    public function update(Request $request, MosqueSetting $masjid, MosqueCommitteeMember $committeeMember): RedirectResponse
    {
        $this->ensureOwnedBy($masjid, $committeeMember);
        $data = $this->validated($request);
        if ($request->hasFile('photo')) {
            $oldPhoto = $committeeMember->photo_path;
            $data['photo_path'] = $request->file('photo')->store('committee-members', 'public');
            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }
        $committeeMember->update($data);

        return redirect()->route('admin.masjids.committee-members.index', $masjid)->with('success', 'Committee member updated.');
    }

    public function destroy(MosqueSetting $masjid, MosqueCommitteeMember $committeeMember): RedirectResponse
    {
        $this->ensureOwnedBy($masjid, $committeeMember);
        if ($committeeMember->photo_path) {
            Storage::disk('public')->delete($committeeMember->photo_path);
        }
        $committeeMember->delete();

        return redirect()->route('admin.masjids.committee-members.index', $masjid)->with('success', 'Committee member deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'position' => ['required', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'show_phone_publicly' => ['required', 'boolean'],
            'show_email_publicly' => ['required', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function ensureOwnedBy(MosqueSetting $masjid, MosqueCommitteeMember $member): void
    {
        abort_unless($member->mosque_setting_id === $masjid->id, 404);
    }
}
