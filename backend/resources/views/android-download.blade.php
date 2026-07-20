<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Android TV Download | bayanDigital</title></head>
<body style="font-family:system-ui,sans-serif;background:#071a2d;color:white;padding:48px">
    <a href="{{ route('landing') }}" style="color:white;font-size:25px;font-weight:650;letter-spacing:-.065em;text-decoration:none">bayan<span style="color:#ffd166;font-weight:950">Digital</span></a>
    <h1>Android TV Application</h1>
    <p>Version: <strong>{{ $android['version_name'] }}</strong></p>
    <p>Build code: <strong>{{ $android['version_code'] }}</strong></p>
    <p>{{ $android['release_notes'] }}</p>
    <p><a style="display:inline-block;padding:14px 22px;border-radius:999px;background:#ffd166;color:#071a2d;font-weight:900;text-decoration:none" href="{{ $android['apk_url'] }}" download>Download Android TV APK</a></p>
    <p>After installation, open setup and enter the Masjid/Surau Unique ID issued after registration.</p>
</body>
</html>
