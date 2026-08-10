@extends('admin.layout')
@section('title', 'Global notices')
@section('subtitle', 'Urgent information published to every approved masjid and surau screen and public page.')
@section('top-action')<a class="button" href="{{ route('admin.global-notices.create') }}">+ Publish notice</a>@endsection
@section('content')
<section class="panel">
    <div class="panel-head"><h2>All global notices</h2><span class="secondary-text">Use an end date so time-sensitive alerts expire automatically.</span></div>
    @if($notices->isEmpty())<div class="empty">There are no global notices. Publish one for disasters, severe weather, closures, or other urgent information.</div>@else
    <div class="table-wrap"><table><thead><tr><th>Notice</th><th>Status</th><th>Display period</th><th>Actions</th></tr></thead><tbody>
    @foreach($notices as $notice)<tr>
        <td><div class="primary-text">{{ $notice->title }}</div><div class="secondary-text">{{ Str::limit($notice->body, 90) }}</div></td>
        <td><span class="badge {{ $notice->is_active ? 'active' : 'inactive' }}">{{ $notice->is_active ? 'Active' : 'Disabled' }}</span></td>
        <td><div>{{ $notice->starts_at?->format('d M Y, H:i') ?: 'Immediately' }}</div><div class="secondary-text">until {{ $notice->ends_at?->format('d M Y, H:i') ?: 'removed manually' }}</div></td>
        <td><div class="actions-inline"><a class="button secondary small" href="{{ route('admin.global-notices.edit', $notice) }}">Edit</a><form method="POST" action="{{ route('admin.global-notices.destroy', $notice) }}" onsubmit="return confirm('Remove this notice from every screen and public page?')">@csrf @method('DELETE')<button class="button danger small" type="submit">Remove</button></form></div></td>
    </tr>@endforeach
    </tbody></table></div>
    <div class="pager">{{ $notices->links() }}</div>
    @endif
</section>
@endsection
