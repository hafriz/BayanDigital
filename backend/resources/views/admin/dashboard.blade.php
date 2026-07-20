@extends('admin.layout')
@section('title', 'Overview')
@section('subtitle', 'A quick view of registrations, accounts, and active TV content.')
@section('top-action')<a class="button" href="{{ route('admin.masjids.index', ['status' => 'pending']) }}">Review registrations</a>@endsection
@section('content')
<section class="stats">
    <article class="stat"><span>Pending review</span><strong>{{ $stats['pending'] }}</strong></article>
    <article class="stat"><span>Approved masjids</span><strong>{{ $stats['approved'] }}</strong></article>
    <article class="stat"><span>Active content</span><strong>{{ $stats['contents'] }}</strong></article>
    <article class="stat"><span>Active users</span><strong>{{ $stats['users'] }}</strong></article>
</section>
<section class="panel">
    <div class="panel-head"><h2>Recent registrations</h2><a class="button secondary small" href="{{ route('admin.masjids.index') }}">View all</a></div>
    @if($recentMasjids->isEmpty())
        <div class="empty">No masjid or surau registrations yet.</div>
    @else
        <div class="table-wrap"><table><thead><tr><th>Masjid / Surau</th><th>JAKIM zone</th><th>Status</th><th>Registered</th><th></th></tr></thead><tbody>
        @foreach($recentMasjids as $masjid)<tr>
            <td><div class="primary-text">{{ $masjid->name }}</div><div class="secondary-text">{{ $masjid->public_id }}</div></td>
            <td>{{ $masjid->zone_code }}</td><td><span class="badge {{ $masjid->status }}">{{ $masjid->status }}</span></td>
            <td>{{ $masjid->created_at->diffForHumans() }}</td><td><a class="button secondary small" href="{{ route('admin.masjids.edit', $masjid) }}">Manage</a></td>
        </tr>@endforeach
        </tbody></table></div>
    @endif
</section>
@endsection
