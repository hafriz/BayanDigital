<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Registration Submitted | bayanDigital</title></head>
<body style="font-family:system-ui,sans-serif;background:#071a2d;color:white;padding:48px">
    <a href="{{ route('landing') }}" style="color:white;font-size:25px;font-weight:650;letter-spacing:-.065em;text-decoration:none">bayan<span style="color:#ffd166;font-weight:950">Digital</span></a>
    <h1>Registration Submitted</h1>
    <p>{{ ucfirst($masjid->type) }}: <strong>{{ $masjid->name }}</strong></p>
    <p>Status: <strong>{{ ucfirst($masjid->status) }}</strong></p>
    <p>Android Setup Unique ID: <strong style="color:#ffd166">{{ $masjid->public_id }}</strong></p>
    <p>Use this ID on the Android TV app after your registration is approved by the SaaS administrator.</p>
</body>
</html>
