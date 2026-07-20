@extends('admin.layout')
@section('title', 'TV content')
@section('subtitle', $masjid->name.' · Organize what appears on the smart screen')
@section('top-action')<a class="button" href="{{ route('admin.masjids.contents.create', $masjid) }}">+ Add content</a>@endsection
@section('content')
<div style="margin-bottom:15px"><a href="{{ route('admin.masjids.edit', $masjid) }}" style="color:var(--emerald);font-weight:800;text-decoration:none">← Back to screen settings</a></div>
@php
    $categories = [
        'announcement' => ['Announcements', 'Important messages shown as information cards.', '▣'],
        'ticker' => ['Running ticker', 'Short messages moving along the bottom of the TV.', '→'],
        'slide' => ['Slides', 'Rotating campaign, programme, or event panels.', '▤'],
        'image' => ['Images', 'Visual posters and remotely hosted media.', '▧'],
    ];
@endphp
<section class="category-grid">
    @foreach($categories as $value => [$label, $description, $icon])
        <article class="panel" style="border-color:{{ $type === $value ? 'var(--emerald)' : 'var(--line)' }}">
            <a href="{{ route('admin.masjids.contents.index', [$masjid, 'type' => $value]) }}" style="display:block;padding:18px;text-decoration:none">
                <div style="display:flex;justify-content:space-between;align-items:center"><strong style="font-size:17px;color:var(--navy)">{{ $icon }} {{ $label }}</strong><span class="badge">{{ $typeCounts[$value] ?? 0 }}</span></div>
                <p class="secondary-text" style="min-height:38px">{{ $description }}</p>
            </a>
            <div style="padding:0 18px 16px"><a class="button secondary small" href="{{ route('admin.masjids.contents.create', [$masjid, 'type' => $value]) }}">+ Add {{ strtolower($label) }}</a></div>
        </article>
    @endforeach
</section>
<div class="actions-inline" style="margin-bottom:12px"><a class="button {{ $type === null ? '' : 'secondary' }} small" href="{{ route('admin.masjids.contents.index', $masjid) }}">All content</a>@foreach($categories as $value => [$label])<a class="button {{ $type === $value ? '' : 'secondary' }} small" href="{{ route('admin.masjids.contents.index', [$masjid, 'type' => $value]) }}">{{ $label }}</a>@endforeach</div>
<section class="panel">
    <div class="panel-head"><h2>{{ $type ? $categories[$type][0] : 'All TV content' }}</h2><span class="secondary-text">Lower display-order numbers appear first.</span></div>
    @if($contents->isEmpty())<div class="empty">No {{ $type ? strtolower($categories[$type][0]) : 'screen content' }} yet. Choose a category above to add one.</div>@else
    <div class="table-wrap"><table><thead><tr><th>Content</th><th>Type</th><th>Status</th><th class="hide-mobile">Schedule</th><th>Order</th><th>Actions</th></tr></thead><tbody>
    @foreach($contents as $content)<tr>
        <td><div class="primary-text">{{ $content->title ?: 'Untitled content' }}</div><div class="secondary-text">{{ Illuminate\Support\Str::limit($content->body ?: $content->media_path, 65) }}</div></td>
        <td><span class="badge">{{ $categories[$content->type][0] ?? $content->type }}</span></td><td><span class="badge {{ $content->is_active ? 'active' : 'inactive' }}">{{ $content->is_active ? 'Active' : 'Disabled' }}</span></td>
        <td class="hide-mobile"><div>{{ $content->starts_at?->format('d M Y, H:i') ?: 'Immediately' }}</div><div class="secondary-text">until {{ $content->ends_at?->format('d M Y, H:i') ?: 'no end date' }}</div></td>
        <td>{{ $content->sort_order }}</td><td><div class="actions-inline"><a class="button secondary small" href="{{ route('admin.masjids.contents.edit', [$masjid, $content]) }}">Edit</a><form method="POST" action="{{ route('admin.masjids.contents.destroy', [$masjid, $content]) }}" onsubmit="return confirm('Delete this screen content?')">@csrf @method('DELETE')<button class="button danger small" type="submit">Delete</button></form></div></td>
    </tr>@endforeach
    </tbody></table></div>
    <div class="pager"><span>Page {{ $contents->currentPage() }} of {{ $contents->lastPage() }}</span><div class="actions-inline">@if($contents->previousPageUrl())<a class="button secondary small" href="{{ $contents->previousPageUrl() }}">Previous</a>@endif @if($contents->nextPageUrl())<a class="button secondary small" href="{{ $contents->nextPageUrl() }}">Next</a>@endif</div></div>
    @endif
</section>
@endsection
