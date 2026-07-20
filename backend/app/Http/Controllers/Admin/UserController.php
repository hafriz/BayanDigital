<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->orderBy('name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['managedUser' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::query()->create($this->validated($request));

        return redirect()->route('admin.users.index')->with('success', 'User account created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', ['managedUser' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validated($request, $user);

        if ($request->user()->is($user) && ($validated['role'] !== User::ROLE_ADMIN || ! $validated['is_active'])) {
            return back()->withErrors(['role' => 'You cannot demote or disable your own account.'])->withInput();
        }

        if ($user->isAdmin()
            && ($validated['role'] !== User::ROLE_ADMIN || ! $validated['is_active'])
            && User::query()->where('role', User::ROLE_ADMIN)->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['role' => 'The final active administrator cannot be demoted or disabled.'])->withInput();
        }

        if ($validated['password'] === null) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User account updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'You cannot delete your own account.');

        if ($user->isAdmin() && User::query()->where('role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->withErrors(['user' => 'The final administrator cannot be deleted.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User account deleted.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $passwordRules = $user
            ? ['nullable', 'string', 'min:12', 'confirmed']
            : ['required', 'string', 'min:12', 'confirmed'];

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_OPERATOR])],
            'is_active' => ['required', 'boolean'],
            'password' => $passwordRules,
        ]);
    }
}
