<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Register Masjid / Surau</title></head>
<body style="font-family:system-ui,sans-serif;background:#f6f8fb;color:#102a43;padding:32px">
    <h1>Register Masjid / Surau</h1>
    @if ($errors->any())<div style="color:#b00020"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="post" action="{{ route('masjids.store') }}" style="display:grid;gap:12px;max-width:680px">
        @csrf
        <label>Name <input name="name" value="{{ old('name') }}" required></label>
        <label>Type <select name="type"><option value="masjid">Masjid</option><option value="surau">Surau</option></select></label>
        <label>JAKIM Zone Code <input name="zone_code" value="{{ old('zone_code', 'SGR01') }}" required></label>
        <label>Contact Name <input name="contact_name" value="{{ old('contact_name') }}" required></label>
        <label>Contact Phone <input name="contact_phone" value="{{ old('contact_phone') }}" required></label>
        <label>Contact Email <input name="contact_email" type="email" value="{{ old('contact_email') }}"></label>
        <label>Address <textarea name="address">{{ old('address') }}</textarea></label>
        <button type="submit">Submit Registration</button>
    </form>
</body>
</html>
