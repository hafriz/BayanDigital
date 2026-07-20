@extends('admin.layout')
@section('title', 'Users')
@section('subtitle', 'Control who can access the backend and what they are allowed to manage.')
@section('top-action')<a class="button" href="{{ route('admin.users.create') }}">+ Create user</a>@endsection
@section('content')
<section class="panel">
    @if($users->isEmpty())<div class="empty">No user accounts found.</div>@else
    <div class="table-wrap"><table><thead><tr><th>User</th><th>Role</th><th>Status</th><th class="hide-mobile">Created</th><th>Actions</th></tr></thead><tbody>
    @foreach($users as $managedUser)<tr>
        <td><div class="primary-text">{{ $managedUser->name }}</div><div class="secondary-text">{{ $managedUser->email }}</div></td>
        <td><span class="badge {{ $managedUser->role }}">{{ $managedUser->role }}</span></td>
        <td><span class="badge {{ $managedUser->is_active ? 'active' : 'inactive' }}">{{ $managedUser->is_active ? 'Active' : 'Disabled' }}</span></td>
        <td class="hide-mobile">{{ $managedUser->created_at->format('d M Y') }}</td>
        <td><div class="actions-inline"><a class="button secondary small" href="{{ route('admin.users.edit', $managedUser) }}">Edit</a>
        @unless(auth()->user()->is($managedUser))<form method="POST" action="{{ route('admin.users.destroy', $managedUser) }}" onsubmit="return confirm('Delete this user account?')">@csrf @method('DELETE')<button class="button danger small" type="submit">Delete</button></form>@endunless</div></td>
    </tr>@endforeach
    </tbody></table></div>
    <div class="pager"><span>Page {{ $users->currentPage() }} of {{ $users->lastPage() }}</span><div class="actions-inline">@if($users->previousPageUrl())<a class="button secondary small" href="{{ $users->previousPageUrl() }}">Previous</a>@endif @if($users->nextPageUrl())<a class="button secondary small" href="{{ $users->nextPageUrl() }}">Next</a>@endif</div></div>
    @endif
</section>
@endsection
