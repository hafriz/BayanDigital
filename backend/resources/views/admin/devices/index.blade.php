@extends('admin.layout')
@section('title', 'TV devices')
@section('subtitle', $masjid->name.' · Secure pairing approvals')
@section('content')
<div style="margin-bottom:15px"><a href="{{ route('admin.masjids.edit', $masjid) }}" style="color:var(--emerald);font-weight:800;text-decoration:none">← Back to masjid settings</a></div>
<section class="panel">
    <div class="panel-head"><div><h2>Pairing and access</h2><div class="secondary-text">Approve only when the six-digit code matches the code shown on the physical TV.</div></div></div>
    @if($devices->isEmpty())
        <div class="empty">No TV pairing requests yet. Open the Android app, search for this location, and select it to create a request.</div>
    @else
        <div class="table-wrap"><table><thead><tr><th>TV device</th><th>Pairing code</th><th>Status</th><th>Activity</th><th>Actions</th></tr></thead><tbody>
        @foreach($devices as $device)<tr>
            <td><div class="primary-text">{{ $device->device_name ?: 'Android TV' }}</div><div class="secondary-text">Requested {{ $device->created_at->diffForHumans() }}</div></td>
            <td><strong style="font-size:20px;letter-spacing:.12em">{{ $device->pairing_code }}</strong><div class="secondary-text">Expires {{ $device->expires_at->diffForHumans() }}</div></td>
            <td><span class="badge {{ in_array($device->status, ['approved']) ? 'active' : ($device->status === 'pending' ? 'pending' : 'inactive') }}">{{ $device->status }}</span>@if($device->approver)<div class="secondary-text">by {{ $device->approver->name }}</div>@endif</td>
            <td><div>{{ $device->last_seen_at?->diffForHumans() ?: 'Never connected' }}</div>@if($device->approved_at)<div class="secondary-text">Approved {{ $device->approved_at->diffForHumans() }}</div>@endif</td>
            <td><div class="actions-inline">
                @if($device->status === 'pending' && $device->expires_at->isFuture())
                    <form method="POST" action="{{ route('admin.masjids.devices.approve', [$masjid, $device]) }}">@csrf<button class="button small" type="submit">Approve</button></form>
                    <form method="POST" action="{{ route('admin.masjids.devices.reject', [$masjid, $device]) }}">@csrf<button class="button danger small" type="submit">Reject</button></form>
                @elseif($device->status === 'approved')
                    <form method="POST" action="{{ route('admin.masjids.devices.revoke', [$masjid, $device]) }}" onsubmit="return confirm('Revoke this TV immediately?')">@csrf<button class="button danger small" type="submit">Revoke</button></form>
                @endif
            </div></td>
        </tr>@endforeach
        </tbody></table></div>
        <div class="pager"><span>Page {{ $devices->currentPage() }} of {{ $devices->lastPage() }}</span><div class="actions-inline">@if($devices->previousPageUrl())<a class="button secondary small" href="{{ $devices->previousPageUrl() }}">Previous</a>@endif @if($devices->nextPageUrl())<a class="button secondary small" href="{{ $devices->nextPageUrl() }}">Next</a>@endif</div></div>
    @endif
</section>
@endsection
