@extends('admin.layout')
@section('title', 'Masjid settings')
@section('subtitle', $masjid->name.' · '.$masjid->public_id)
@section('top-action')<div class="actions-inline"><a class="button secondary" href="{{ route('admin.masjids.committee-members.index', $masjid) }}">Committee members</a>@if(auth()->user()->isAdmin())<a class="button secondary" href="{{ route('admin.masjids.devices.index', $masjid) }}">Paired TVs</a>@endif<a class="button" href="{{ route('admin.masjids.contents.index', $masjid) }}">Manage TV content</a></div>@endsection
@section('content')
<section class="panel"><div class="panel-body">
    <form method="POST" action="{{ route('admin.masjids.update', $masjid) }}">@csrf @method('PUT')
        <div class="form-grid">
            <div class="field"><label for="name">Display name</label><input id="name" name="name" value="{{ old('name', $masjid->name) }}" required></div>
            <div class="field"><label for="public_slug">Public portal slug</label><input id="public_slug" name="public_slug" value="{{ old('public_slug', $masjid->public_slug) }}" required><small>Public page: /masjid/{{ $masjid->public_slug }}</small></div>
            <div class="field"><label for="type">Location type</label><select id="type" name="type" required><option value="masjid" @selected(old('type', $masjid->type) === 'masjid')>Masjid</option><option value="surau" @selected(old('type', $masjid->type) === 'surau')>Surau</option></select></div>
            <div class="field"><label for="status">Registration status</label><select id="status" name="status" required>@foreach(['pending','approved','suspended','rejected'] as $option)<option value="{{ $option }}" @selected(old('status', $masjid->status) === $option)>{{ ucfirst($option) }}</option>@endforeach</select><small>Only approved sites can sync settings to Android TV.</small></div>
            <div class="field"><label for="zone_code">JAKIM prayer zone</label><select id="zone_code" name="zone_code" required>@foreach($jakimZones as $state => $zones)<optgroup label="{{ $state }}">@foreach($zones as $code => $label)<option value="{{ $code }}" @selected(old('zone_code', $masjid->zone_code) === $code)>{{ $code }} — {{ $label }}</option>@endforeach</optgroup>@endforeach</select></div>
            <div class="field"><label for="contact_name">Contact name</label><input id="contact_name" name="contact_name" value="{{ old('contact_name', $masjid->contact_name) }}"></div>
            <div class="field"><label for="contact_phone">Contact phone</label><input id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $masjid->contact_phone) }}"></div>
            <div class="field"><label for="contact_email">Contact email</label><input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $masjid->contact_email) }}"></div>
            <div class="field"><label for="silent_mode_minutes">Silent mode after prayer (minutes)</label><input id="silent_mode_minutes" name="silent_mode_minutes" type="number" min="0" max="120" value="{{ old('silent_mode_minutes', $masjid->silent_mode_minutes) }}" required></div>
            <div class="field"><label for="prayer_alerts_enabled">Prayer sound alerts</label><select id="prayer_alerts_enabled" name="prayer_alerts_enabled" required><option value="1" @selected((string) old('prayer_alerts_enabled', (int) ($masjid->prayer_alerts_enabled ?? true)) === '1')>Enabled</option><option value="0" @selected((string) old('prayer_alerts_enabled', (int) ($masjid->prayer_alerts_enabled ?? true)) === '0')>Disabled</option></select><small>Controls pre-prayer, azan, iqamah, and countdown beeps on paired TVs.</small></div>
            <div class="field"><label for="pre_prayer_beep_minutes">Pre-prayer beep (minutes before azan)</label><input id="pre_prayer_beep_minutes" name="pre_prayer_beep_minutes" type="number" min="0" max="60" value="{{ old('pre_prayer_beep_minutes', $masjid->pre_prayer_beep_minutes ?? 5) }}" required></div>
            <div class="field full"><label>Iqamah delay after azan (minutes)</label><div style="display:grid;grid-template-columns:repeat(5,minmax(110px,1fr));gap:12px">@foreach(['subuh' => 'Subuh', 'zohor' => 'Zohor', 'asar' => 'Asar', 'maghrib' => 'Maghrib', 'isyak' => 'Isyak'] as $prayer => $label)<div><label for="iqamah_{{ $prayer }}">{{ $label }}</label><input id="iqamah_{{ $prayer }}" name="iqamah_minutes[{{ $prayer }}]" type="number" min="0" max="120" value="{{ old('iqamah_minutes.'.$prayer, data_get($masjid->iqamah_minutes, $prayer, 10)) }}" required></div>@endforeach</div><small>Only the five obligatory prayers are stored; Syuruk and other timeline entries are excluded.</small></div>
            <div class="field"><label for="time_format">Clock format</label><select id="time_format" name="time_format" required><option value="24h" @selected(old('time_format', $masjid->time_format ?: '24h') === '24h')>24-hour — 19:30</option><option value="12h" @selected(old('time_format', $masjid->time_format) === '12h')>12-hour — 7:30 PM</option></select><small>Controls the live clock and all prayer-time cards.</small></div>
            <div class="field full">
                <label for="logo">Surau / masjid logo</label>
                <div style="margin:8px 0 14px"><img id="logo_preview" src="{{ $logoUrl }}" alt="{{ $usingDefaultLogo ? 'Default bayanDigital logo' : $masjid->name.' logo' }}" style="display:block;max-width:260px;max-height:110px;object-fit:contain;background:#061626;border-radius:10px;padding:10px"></div>
                <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/gif,image/webp" aria-describedby="logo_help">
                <small id="logo_help">Upload a JPG, PNG, GIF, or WebP image up to 2 MB. Uploading a new image safely replaces the current local logo.</small>
                @if(! $usingDefaultLogo)<label style="display:flex;align-items:center;gap:8px;margin-top:12px"><input name="use_default_logo" type="checkbox" value="1" style="width:auto"> Revert to the default bayanDigital logo</label>@endif
            </div>
            <div class="field full">
                <label for="donation_qr_image">Donation QR image</label>
                <input id="donation_qr_image" name="donation_qr_image" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="donation_qr_help">
                <small id="donation_qr_help">JPEG, PNG, or WebP, up to 2 MB. A square QR image works best.</small>
                <div style="margin-top:12px">
                    <img id="donation_qr_preview" src="{{ $masjid->donation_qr_image ? Storage::disk('public')->url($masjid->donation_qr_image) : $masjid->donation_qr_url }}" alt="Donation QR image preview" style="{{ $masjid->donation_qr_image || $masjid->donation_qr_url ? '' : 'display:none;' }}width:min(240px,100%);aspect-ratio:1;object-fit:contain;border-radius:14px;border:1px solid #d1d5db;background:#fff;padding:8px">
                </div>
                @if($masjid->donation_qr_image || $masjid->donation_qr_url)
                    <label style="display:flex;align-items:center;gap:8px;margin-top:10px"><input type="checkbox" name="remove_donation_qr" value="1" style="width:auto"> Delete the current donation QR image</label>
                @endif
            </div>
            <div class="field"><label for="donation_caption">Donation caption</label><input id="donation_caption" name="donation_caption" maxlength="200" value="{{ old('donation_caption', $masjid->donation_caption) }}" placeholder="Support our masjid programmes"></div>
            <div class="field"><label for="donation_account">Donation account</label><input id="donation_account" name="donation_account" maxlength="150" value="{{ old('donation_account', $masjid->donation_account) }}" placeholder="Bank name · 1234 5678 90"></div>
            <div class="field full"><label for="committee">Committee members</label><textarea id="committee" name="committee" placeholder="Chairperson — Ahmad bin Ali&#10;Treasurer — Fatimah Ahmad">{{ old('committee', implode("\n", $masjid->committee ?? [])) }}</textarea><small>Enter one role and member per line.</small></div>
            <div class="field full"><label for="google_calendar_ics_url">Public Google Calendar iCal address</label><input id="google_calendar_ics_url" name="google_calendar_ics_url" type="url" value="{{ old('google_calendar_ics_url', $masjid->google_calendar_ics_url) }}" placeholder="https://calendar.google.com/calendar/ical/.../public/basic.ics"><small>Optional. In Google Calendar, make the calendar public, then copy “Public address in iCal format” from Integrate calendar. Upcoming events become timetable cards automatically.</small></div>
            <div class="field"><label for="screen_sleep_enabled">Automatic screen schedule</label><select id="screen_sleep_enabled" name="screen_sleep_enabled" required><option value="0" @selected((string) old('screen_sleep_enabled', (int) $masjid->screen_sleep_enabled) === '0')>Disabled — always on</option><option value="1" @selected((string) old('screen_sleep_enabled', (int) $masjid->screen_sleep_enabled) === '1')>Enabled</option></select><small>Blackens the display and allows Android TV to enter standby overnight.</small></div>
            <div class="field"><label for="screen_sleep_mode">Screen off method</label><select id="screen_sleep_mode" name="screen_sleep_mode" required><option value="fixed" @selected(old('screen_sleep_mode', $masjid->screen_sleep_mode ?: 'fixed') === 'fixed')>At a fixed time</option><option value="after_isyak" @selected(old('screen_sleep_mode', $masjid->screen_sleep_mode) === 'after_isyak')>After Azan Isyak</option></select></div>
            <div class="field"><label for="screen_sleep_time">Fixed screen off time</label><input id="screen_sleep_time" name="screen_sleep_time" type="time" value="{{ old('screen_sleep_time', substr($masjid->screen_sleep_time ?: '22:00', 0, 5)) }}" required><small>Used when “fixed time” is selected.</small></div>
            <div class="field"><label for="sleep_after_isyak_minutes">Sleep after Isyak (minutes)</label><input id="sleep_after_isyak_minutes" name="sleep_after_isyak_minutes" type="number" min="0" max="180" value="{{ old('sleep_after_isyak_minutes', $masjid->sleep_after_isyak_minutes ?? 30) }}" required><small>For example, 30 switches off 30 minutes after Isyak.</small></div>
            <div class="field"><label for="screen_wake_mode">Morning wake method</label><select id="screen_wake_mode" name="screen_wake_mode" required><option value="fixed" @selected(old('screen_wake_mode', $masjid->screen_wake_mode ?: 'fixed') === 'fixed')>At a fixed time</option><option value="before_subuh" @selected(old('screen_wake_mode', $masjid->screen_wake_mode) === 'before_subuh')>Before Azan Subuh</option></select></div>
            <div class="field"><label for="screen_wake_time">Fixed wake time</label><input id="screen_wake_time" name="screen_wake_time" type="time" value="{{ old('screen_wake_time', substr($masjid->screen_wake_time ?: '05:00', 0, 5)) }}" required><small>Used when “fixed time” is selected.</small></div>
            <div class="field"><label for="wake_before_subuh_minutes">Wake before Subuh (minutes)</label><input id="wake_before_subuh_minutes" name="wake_before_subuh_minutes" type="number" min="0" max="180" value="{{ old('wake_before_subuh_minutes', $masjid->wake_before_subuh_minutes ?? 30) }}" required><small>For example, 30 wakes the screen 30 minutes before Subuh.</small></div>
            <div class="field"><label for="screen_rotation_enabled">Fullscreen view rotation</label><select id="screen_rotation_enabled" name="screen_rotation_enabled" required><option value="0" @selected((string) old('screen_rotation_enabled', (int) $masjid->screen_rotation_enabled) === '0')>Disabled — always show dashboard</option><option value="1" @selected((string) old('screen_rotation_enabled', (int) $masjid->screen_rotation_enabled) === '1')>Enabled</option></select><small>Slides between fullscreen views when not near prayer time.</small></div>
            <div class="field"><label for="clock_style">Clock size</label><select id="clock_style" name="clock_style" required><option value="standard" @selected(old('clock_style', $masjid->clock_style ?: 'standard') === 'standard')>Standard</option><option value="big" @selected(old('clock_style', $masjid->clock_style) === 'big')>Big</option></select><small>Controls the clock size on the main dashboard and fullscreen clock.</small></div>
            <div class="field full"><label>Fullscreen views to rotate</label><div style="display:grid;grid-template-columns:repeat(4,minmax(140px,1fr));gap:12px">
                @foreach(['clock' => 'Clock', 'announcements' => 'Pengumuman', 'schedule' => 'Jadual Solat', 'donation' => 'Sumbangan'] as $value => $label)
                    <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="rotation_views[]" value="{{ $value }}" style="width:auto" @checked(in_array($value, old('rotation_views', $masjid->rotation_views ?? ['clock', 'announcements', 'schedule', 'donation'])))>{{ $label }}</label>
                @endforeach
            </div><small>Each enabled view fills the screen for the duration below, then slides to the next.</small></div>
            <div class="field"><label for="rotation_duration_minutes">Minutes per fullscreen view</label><input id="rotation_duration_minutes" name="rotation_duration_minutes" type="number" min="1" max="60" value="{{ old('rotation_duration_minutes', $masjid->rotation_duration_minutes ?? 3) }}" required><small>How long each view stays before sliding to the next.</small></div>
            <div class="field"><label for="rotation_near_prayer_minutes">Stop rotating before Azan (minutes)</label><input id="rotation_near_prayer_minutes" name="rotation_near_prayer_minutes" type="number" min="5" max="180" value="{{ old('rotation_near_prayer_minutes', $masjid->rotation_near_prayer_minutes ?? 30) }}" required><small>Near prayer time the screen sticks to the main dashboard until after Iqamah/silent mode.</small></div>
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

@push('scripts')
<script>
document.getElementById('logo').addEventListener('change', function () {
    const preview = document.getElementById('logo_preview');
    const file = this.files && this.files[0];
    if (!file) return;
    preview.src = URL.createObjectURL(file);
    preview.onload = () => URL.revokeObjectURL(preview.src);
});
document.getElementById('donation_qr_image').addEventListener('change', function () {
    const preview = document.getElementById('donation_qr_preview');
    const file = this.files && this.files[0];
    if (!file) return;
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
    preview.onload = () => URL.revokeObjectURL(preview.src);
});
</script>
@endpush
