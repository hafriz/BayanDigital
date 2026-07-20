@extends('admin.layout')
@section('title', 'Masjids')
@section('subtitle', 'Review registrations, approve access, and manage each Android TV screen.')
@section('content')
<section class="panel" style="margin-bottom:20px"><div class="panel-body">
    <form class="filters" method="GET" action="{{ route('admin.masjids.index') }}">
        <input name="search" value="{{ $search }}" placeholder="Search name, ID, or contact…" aria-label="Search masjids">
        <select name="status" aria-label="Filter by status"><option value="">All statuses</option>@foreach(['pending','approved','suspended','rejected'] as $option)<option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>@endforeach</select>
        <button class="button" type="submit">Filter</button>
    </form>
</div></section>
<section class="panel">
    @if($masjids->isEmpty())<div class="empty">No matching masjid or surau registrations.</div>@else
    <div class="table-wrap"><table><thead><tr><th>Masjid / Surau</th><th>Zone</th><th>Status</th><th class="hide-mobile">Contact</th><th>TV content</th><th>Actions</th></tr></thead><tbody>
    @foreach($masjids as $masjid)<tr>
        <td><div class="primary-text">{{ $masjid->name }}</div><div class="secondary-text">{{ ucfirst($masjid->type) }} · {{ $masjid->public_id }}</div></td>
        <td>{{ $masjid->zone_code }}</td><td><span class="badge {{ $masjid->status }}">{{ $masjid->status }}</span></td>
        <td class="hide-mobile"><div>{{ $masjid->contact_name ?: '—' }}</div><div class="secondary-text">{{ $masjid->contact_email ?: $masjid->contact_phone }}</div></td>
        <td>{{ $masjid->screen_contents_count }} item{{ $masjid->screen_contents_count === 1 ? '' : 's' }}</td>
        <td><div class="actions-inline"><a class="button secondary small" href="{{ route('admin.masjids.edit', $masjid) }}">Settings</a><a class="button small" href="{{ route('admin.masjids.contents.index', $masjid) }}">Content</a></div></td>
    </tr>@endforeach
    </tbody></table></div>
    <div class="pager"><span>Page {{ $masjids->currentPage() }} of {{ $masjids->lastPage() }}</span><div class="actions-inline">@if($masjids->previousPageUrl())<a class="button secondary small" href="{{ $masjids->previousPageUrl() }}">Previous</a>@endif @if($masjids->nextPageUrl())<a class="button secondary small" href="{{ $masjids->nextPageUrl() }}">Next</a>@endif</div></div>
    @endif
</section>
@endsection
