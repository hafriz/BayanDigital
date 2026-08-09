@extends('admin.layout')
@section('title', 'Committee members')
@section('subtitle', $masjid->name.' · Control the public committee directory')
@section('top-action')<a class="button" href="{{ route('admin.masjids.committee-members.create', $masjid) }}">+ Add member</a>@endsection
@section('content')
<div style="margin-bottom:15px"><a href="{{ route('admin.masjids.edit', $masjid) }}" style="color:var(--emerald);font-weight:800;text-decoration:none">← Back to masjid settings</a></div>
<section class="panel">
    @if($members->isEmpty())
        <div class="empty">No committee members have been added yet.</div>
    @else
        <div class="table-wrap"><table><thead><tr><th>Order</th><th>Member</th><th>Contact visibility</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @foreach($members as $member)<tr>
            <td>{{ $member->display_order }}</td>
            <td><div class="actions-inline">
                @if($member->photo_path)<img src="{{ Storage::url($member->photo_path) }}" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover">@endif
                <div><div class="primary-text">{{ $member->name }}</div><div class="secondary-text">{{ $member->position }}</div></div>
            </div></td>
            <td><div>{{ $member->phone ?: 'No phone' }} {{ $member->phone && $member->show_phone_publicly ? '· Public' : '' }}</div><div class="secondary-text">{{ $member->email ?: 'No email' }} {{ $member->email && $member->show_email_publicly ? '· Public' : '' }}</div></td>
            <td><span class="badge {{ $member->is_active ? 'active' : 'inactive' }}">{{ $member->is_active ? 'Active' : 'Inactive' }}</span></td>
            <td><div class="actions-inline"><a class="button secondary small" href="{{ route('admin.masjids.committee-members.edit', [$masjid, $member]) }}">Edit</a><form method="POST" action="{{ route('admin.masjids.committee-members.destroy', [$masjid, $member]) }}" onsubmit="return confirm('Delete this committee member?')">@csrf @method('DELETE')<button class="button danger small" type="submit">Delete</button></form></div></td>
        </tr>@endforeach
        </tbody></table></div>
    @endif
</section>
@endsection
