@extends('admin.layout')
@section('title', $managedUser->exists ? 'Edit user' : 'Create user')
@section('subtitle', $managedUser->exists ? 'Update account details, access level, or password.' : 'Add an administrator or operator to the management console.')
@section('content')
<section class="panel"><div class="panel-body">
    <form method="POST" action="{{ $managedUser->exists ? route('admin.users.update', $managedUser) : route('admin.users.store') }}">
        @csrf @if($managedUser->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="field"><label for="name">Full name</label><input id="name" name="name" value="{{ old('name', $managedUser->name) }}" required></div>
            <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email', $managedUser->email) }}" required></div>
            <div class="field"><label for="role">Access role</label><select id="role" name="role" required><option value="operator" @selected(old('role', $managedUser->role ?: 'operator') === 'operator')>Operator — masjids and content</option><option value="admin" @selected(old('role', $managedUser->role) === 'admin')>Administrator — full access</option></select></div>
            <div class="field"><label for="is_active">Account status</label><select id="is_active" name="is_active" required><option value="1" @selected((string) old('is_active', $managedUser->exists ? (int)$managedUser->is_active : 1) === '1')>Active</option><option value="0" @selected((string) old('is_active', $managedUser->exists ? (int)$managedUser->is_active : 1) === '0')>Disabled</option></select></div>
            <div class="field"><label for="password">{{ $managedUser->exists ? 'New password' : 'Password' }}</label><input id="password" name="password" type="password" autocomplete="new-password" {{ $managedUser->exists ? '' : 'required' }}><small>Minimum 12 characters. {{ $managedUser->exists ? 'Leave blank to keep the current password.' : '' }}</small></div>
            <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" {{ $managedUser->exists ? '' : 'required' }}></div>
        </div>
        <div class="form-actions"><button class="button" type="submit">{{ $managedUser->exists ? 'Save changes' : 'Create account' }}</button><a class="button secondary" href="{{ route('admin.users.index') }}">Cancel</a></div>
    </form>
</div></section>
@endsection
