<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Android TV Download</title></head>
<body style="font-family:system-ui,sans-serif;background:#071a2d;color:white;padding:48px">
    <h1>Android TV Application</h1>
    <p>Version: <strong>{{ $android['version_name'] }}</strong></p>
    <p>Build code: <strong>{{ $android['version_code'] }}</strong></p>
    <p>{{ $android['release_notes'] }}</p>
    <p><a style="color:#ffd166;font-weight:800" href="{{ $android['apk_url'] }}">Download APK</a></p>
    <p>After installation, open setup and enter the Masjid/Surau Unique ID issued after registration.</p>
</body>
</html>
