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
            <div class="field"><label for="time_format">Clock format</label><select id="time_format" name="time_format" required><option value="24h" @selected(old('time_format', $masjid->time_format ?: '24h') === '24h')>24-hour — 19:30</option><option value="12h" @selected(old('time_format', $masjid->time_format) === '12h')>12-hour — 7:30 PM</option></select><small>Controls the live clock and all prayer-time cards.</small></div>
            <div class="field full"><label for="logo_url">Surau / masjid logo or image URL</label><input id="logo_url" name="logo_url" type="url" value="{{ old('logo_url', $masjid->logo_url) }}" placeholder="https://example.org/logo.png"><small>Displayed in the Android TV masthead. Leave empty to use the default bayanDigital wordmark.</small></div>
            <div class="field full"><label for="google_calendar_ics_url">Public Google Calendar iCal address</label><input id="google_calendar_ics_url" name="google_calendar_ics_url" type="url" value="{{ old('google_calendar_ics_url', $masjid->google_calendar_ics_url) }}" placeholder="https://calendar.google.com/calendar/ical/.../public/basic.ics"><small>Optional. In Google Calendar, make the calendar public, then copy “Public address in iCal format” from Integrate calendar. Upcoming events become timetable cards automatically.</small></div>
            <div class="field"><label for="screen_sleep_enabled">Automatic screen schedule</label><select id="screen_sleep_enabled" name="screen_sleep_enabled" required><option value="0" @selected((string) old('screen_sleep_enabled', (int) $masjid->screen_sleep_enabled) === '0')>Disabled — always on</option><option value="1" @selected((string) old('screen_sleep_enabled', (int) $masjid->screen_sleep_enabled) === '1')>Enabled</option></select><small>Blackens the display and allows Android TV to enter standby overnight.</small></div>
            <div class="field"><label for="screen_sleep_time">Screen off time</label><input id="screen_sleep_time" name="screen_sleep_time" type="time" value="{{ old('screen_sleep_time', substr($masjid->screen_sleep_time ?: '22:00', 0, 5)) }}" required></div>
            <div class="field"><label for="screen_wake_mode">Morning wake method</label><select id="screen_wake_mode" name="screen_wake_mode" required><option value="fixed" @selected(old('screen_wake_mode', $masjid->screen_wake_mode ?: 'fixed') === 'fixed')>At a fixed time</option><option value="before_subuh" @selected(old('screen_wake_mode', $masjid->screen_wake_mode) === 'before_subuh')>Before Azan Subuh</option></select></div>
            <div class="field"><label for="screen_wake_time">Fixed wake time</label><input id="screen_wake_time" name="screen_wake_time" type="time" value="{{ old('screen_wake_time', substr($masjid->screen_wake_time ?: '05:00', 0, 5)) }}" required><small>Used when “fixed time” is selected.</small></div>
            <div class="field"><label for="wake_before_subuh_minutes">Wake before Subuh (minutes)</label><input id="wake_before_subuh_minutes" name="wake_before_subuh_minutes" type="number" min="0" max="180" value="{{ old('wake_before_subuh_minutes', $masjid->wake_before_subuh_minutes ?? 30) }}" required><small>For example, 30 wakes the screen 30 minutes before Subuh.</small></div>
            <div class="field full"><label for="address">Address</label><textarea id="address" name="address">{{ old('address', $masjid->address) }}</textarea></div>
            <div class="field full"><label>TV screen design</label><div style="display:grid;grid-template-columns:repeat(4,minmax(150px,1fr));gap:12px">
                @foreach([
                    'emerald' => ['Emerald Mihrab', '#071a2d', '#0f766e', '#ffd166'],
                    'midnight' => ['Midnight Blue', '#050816', '#253b80', '#67e8f9'],
                    'sand' => ['Warm Sand', '#2b2118', '#8a5d2c', '#f6c85f'],
                    'royal' => ['Royal Violet', '#140c2e', '#6d28d9', '#f0c75e'],
                ] as $value => [$label, $background, $panel, $accent])
                    <label style="display:block;cursor:pointer"><input type="radio" name="screen_theme" value="{{ $value }}" style="width:auto;margin-right:6px" @checked(old('screen_theme', $masjid->screen_theme ?: 'emerald') === $value) required><strong>{{ $label }}</strong><span style="display:block;height:75px;margin-top:8px;border-radius:12px;padding:12px;background:{{ $background }}"><span style="display:block;height:28px;border-radius:8px;background:{{ $panel }};border-left:5px solid {{ $accent }}"></span></span></label>
                @endforeach
            </div><small>Each design changes the full TV palette, cards, highlights, and announcement panels.</small></div>
        </div>
        <div class="form-actions"><button class="button" type="submit">Save masjid settings</button><a class="button secondary" href="{{ route('admin.masjids.index') }}">Cancel</a></div>
    </form>
</div></section>
@endsection
