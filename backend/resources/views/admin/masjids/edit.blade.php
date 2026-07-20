@extends('admin.layout')
@section('title', 'Masjid settings')
@section('subtitle', $masjid->name.' · '.$masjid->public_id)
@section('top-action')<div class="actions-inline">@if(auth()->user()->isAdmin())<a class="button secondary" href="{{ route('admin.masjids.devices.index', $masjid) }}">Paired TVs</a>@endif<a class="button" href="{{ route('admin.masjids.contents.index', $masjid) }}">Manage TV content</a></div>@endsection
@section('content')
<section class="panel"><div class="panel-body">
    <form method="POST" action="{{ route('admin.masjids.update', $masjid) }}">@csrf @method('PUT')
        <div class="form-grid">
            <div class="field"><label for="name">Display name</label><input id="name" name="name" value="{{ old('name', $masjid->name) }}" required></div>
            <div class="field"><label for="type">Location type</label><select id="type" name="type" required><option value="masjid" @selected(old('type', $masjid->type) === 'masjid')>Masjid</option><option value="surau" @selected(old('type', $masjid->type) === 'surau')>Surau</option></select></div>
            <div class="field"><label for="status">Registration status</label><select id="status" name="status" required>@foreach(['pending','approved','suspended','rejected'] as $option)<option value="{{ $option }}" @selected(old('status', $masjid->status) === $option)>{{ ucfirst($option) }}</option>@endforeach</select><small>Only approved sites can sync settings to Android TV.</small></div>
            <div class="field"><label for="zone_code">JAKIM prayer zone</label><select id="zone_code" name="zone_code" required>@foreach($jakimZones as $state => $zones)<optgroup label="{{ $state }}">@foreach($zones as $code => $label)<option value="{{ $code }}" @selected(old('zone_code', $masjid->zone_code) === $code)>{{ $code }} — {{ $label }}</option>@endforeach</optgroup>@endforeach</select></div>
            <div class="field"><label for="contact_name">Contact name</label><input id="contact_name" name="contact_name" value="{{ old('contact_name', $masjid->contact_name) }}"></div>
            <div class="field"><label for="contact_phone">Contact phone</label><input id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $masjid->contact_phone) }}"></div>
            <div class="field"><label for="contact_email">Contact email</label><input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $masjid->contact_email) }}"></div>
            <div class="field"><label for="silent_mode_minutes">Silent mode after prayer (minutes)</label><input id="silent_mode_minutes" name="silent_mode_minutes" type="number" min="0" max="120" value="{{ old('silent_mode_minutes', $masjid->silent_mode_minutes) }}" required></div>
            <div class="field full"><label for="address">Address</label><textarea id="address" name="address">{{ old('address', $masjid->address) }}</textarea></div>
        </div>
        <div class="form-actions"><button class="button" type="submit">Save masjid settings</button><a class="button secondary" href="{{ route('admin.masjids.index') }}">Cancel</a></div>
    </form>
</div></section>
@endsection
