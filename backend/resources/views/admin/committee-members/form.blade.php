@extends('admin.layout')
@section('title', $member->exists ? 'Edit committee member' : 'Add committee member')
@section('subtitle', $masjid->name.' · Ahli Jawatankuasa Masjid')
@section('content')
<section class="panel"><div class="panel-body">
    <form method="POST" enctype="multipart/form-data" action="{{ $member->exists ? route('admin.masjids.committee-members.update', [$masjid, $member]) : route('admin.masjids.committee-members.store', $masjid) }}">
        @csrf @if($member->exists) @method('PUT') @endif
        <div class="form-grid">
            <div class="field"><label for="name">Member name</label><input id="name" name="name" value="{{ old('name', $member->name) }}" maxlength="150" required></div>
            <div class="field"><label for="position">Position / title</label><input id="position" name="position" value="{{ old('position', $member->position) }}" maxlength="150" placeholder="e.g. Pengerusi" required></div>
            <div class="field full"><label for="photo">Photo</label>@if($member->photo_path)<img src="{{ Storage::url($member->photo_path) }}" alt="Current photo of {{ $member->name }}" style="display:block;width:90px;height:90px;object-fit:cover;border-radius:16px;margin-bottom:10px">@endif<input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"><small>Optional JPG, PNG, or WebP image, up to 2 MB. Uploading a new photo replaces the current one.</small></div>
            <div class="field"><label for="phone">Phone number</label><input id="phone" name="phone" type="tel" value="{{ old('phone', $member->phone) }}" maxlength="40"></div>
            <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email', $member->email) }}" maxlength="150"></div>
            <div class="field"><label for="show_phone_publicly">Phone visibility</label><select id="show_phone_publicly" name="show_phone_publicly" required><option value="0" @selected((string) old('show_phone_publicly', (int) $member->show_phone_publicly) === '0')>Private (admin only)</option><option value="1" @selected((string) old('show_phone_publicly', (int) $member->show_phone_publicly) === '1')>Show on public portal</option></select></div>
            <div class="field"><label for="show_email_publicly">Email visibility</label><select id="show_email_publicly" name="show_email_publicly" required><option value="0" @selected((string) old('show_email_publicly', (int) $member->show_email_publicly) === '0')>Private (admin only)</option><option value="1" @selected((string) old('show_email_publicly', (int) $member->show_email_publicly) === '1')>Show on public portal</option></select></div>
            <div class="field"><label for="display_order">Display order</label><input id="display_order" name="display_order" type="number" min="0" max="10000" value="{{ old('display_order', $member->display_order ?? 0) }}" required><small>Lower numbers appear first.</small></div>
            <div class="field"><label for="is_active">Public listing status</label><select id="is_active" name="is_active" required><option value="1" @selected((string) old('is_active', (int) $member->is_active) === '1')>Active</option><option value="0" @selected((string) old('is_active', (int) $member->is_active) === '0')>Inactive</option></select></div>
        </div>
        <div class="form-actions"><button class="button" type="submit">{{ $member->exists ? 'Save member' : 'Add member' }}</button><a class="button secondary" href="{{ route('admin.masjids.committee-members.index', $masjid) }}">Cancel</a></div>
    </form>
</div></section>
@endsection
