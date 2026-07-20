@extends('admin.layout')
@section('title', 'TV content')
@section('subtitle', $masjid->name.' · '.$masjid->public_id)
@section('top-action')<a class="button" href="{{ route('admin.masjids.contents.create', $masjid) }}">+ Add content</a>@endsection
@section('content')
<div style="margin-bottom:15px"><a href="{{ route('admin.masjids.edit', $masjid) }}" style="color:var(--emerald);font-weight:800;text-decoration:none">← Back to masjid settings</a></div>
<section class="panel">
    @if($contents->isEmpty())<div class="empty">No screen content yet. Add an announcement, ticker, slide, or image.</div>@else
    <div class="table-wrap"><table><thead><tr><th>Content</th><th>Type</th><th>Status</th><th class="hide-mobile">Schedule</th><th>Order</th><th>Actions</th></tr></thead><tbody>
    @foreach($contents as $content)<tr>
        <td><div class="primary-text">{{ $content->title ?: 'Untitled content' }}</div><div class="secondary-text">{{ Illuminate\Support\Str::limit($content->body ?: $content->media_path, 65) }}</div></td>
        <td><span class="badge">{{ $content->type }}</span></td><td><span class="badge {{ $content->is_active ? 'active' : 'inactive' }}">{{ $content->is_active ? 'Active' : 'Disabled' }}</span></td>
        <td class="hide-mobile"><div>{{ $content->starts_at?->format('d M Y, H:i') ?: 'Immediately' }}</div><div class="secondary-text">until {{ $content->ends_at?->format('d M Y, H:i') ?: 'no end date' }}</div></td>
        <td>{{ $content->sort_order }}</td><td><div class="actions-inline"><a class="button secondary small" href="{{ route('admin.masjids.contents.edit', [$masjid, $content]) }}">Edit</a><form method="POST" action="{{ route('admin.masjids.contents.destroy', [$masjid, $content]) }}" onsubmit="return confirm('Delete this screen content?')">@csrf @method('DELETE')<button class="button danger small" type="submit">Delete</button></form></div></td>
    </tr>@endforeach
    </tbody></table></div>
    <div class="pager"><span>Page {{ $contents->currentPage() }} of {{ $contents->lastPage() }}</span><div class="actions-inline">@if($contents->previousPageUrl())<a class="button secondary small" href="{{ $contents->previousPageUrl() }}">Previous</a>@endif @if($contents->nextPageUrl())<a class="button secondary small" href="{{ $contents->nextPageUrl() }}">Next</a>@endif</div></div>
    @endif
</section>
@endsection
