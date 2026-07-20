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
<section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:26px;">
    <a href="{{ route('admin.manual') }}" style="display:flex;align-items:center;gap:14px;padding:20px 22px;border:1px solid var(--line);border-radius:16px;background:white;text-decoration:none;color:var(--ink);box-shadow:0 12px 35px rgba(7,24,39,.04);transition:.15s;">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:12px;background:rgba(15,118,110,.08);font-size:22px;flex-shrink:0;">📖</span>
        <div><strong style="display:block;font-size:16px;">User Manual</strong><span style="color:var(--muted);font-size:13px;">Learn how to manage your masjid screens</span></div>
    </a>
    <a href="mailto:support@rarecreation.xyz" style="display:flex;align-items:center;gap:14px;padding:20px 22px;border:1px solid var(--line);border-radius:16px;background:white;text-decoration:none;color:var(--ink);box-shadow:0 12px 35px rgba(7,24,39,.04);transition:.15s;">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:12px;background:rgba(15,118,110,.08);font-size:22px;flex-shrink:0;">✉</span>
        <div><strong style="display:block;font-size:16px;">Need Help?</strong><span style="color:var(--muted);font-size:13px;">support@rarecreation.xyz</span></div>
    </a>
    <a href="https://buymeacoffee.com/rarecreation" target="_blank" rel="noopener" style="display:flex;align-items:center;gap:14px;padding:20px 22px;border:1px solid var(--line);border-radius:16px;background:white;text-decoration:none;color:var(--ink);box-shadow:0 12px 35px rgba(7,24,39,.04);transition:.15s;">
        <span style="display:inline-flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:12px;background:rgba(244,201,93,.15);font-size:22px;flex-shrink:0;">☕</span>
        <div><strong style="display:block;font-size:16px;">Support Me</strong><span style="color:var(--muted);font-size:13px;">Buy Me a Coffee</span></div>
    </a>
</section>
@endsection
