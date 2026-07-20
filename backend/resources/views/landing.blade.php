<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masjid Smart Screen SaaS</title>
    <style>
        body { margin:0; font-family: system-ui, sans-serif; background:#071a2d; color:#fff; }
        .hero { min-height:70vh; display:grid; place-items:center; padding:48px; text-align:center; }
        .card { background:#102a43; border-radius:24px; padding:32px; max-width:920px; box-shadow:0 20px 60px #0008; }
        a.button { display:inline-block; margin:12px; padding:14px 22px; border-radius:999px; background:#ffd166; color:#071a2d; font-weight:800; text-decoration:none; }
        .muted { color:#b8f2e6; }
    </style>
</head>
<body>
<section class="hero">
    <div class="card">
        <h1>Masjid Smart Screen System</h1>
        <p class="muted">SaaS prayer-time CMS for Malaysian masjid and surau Android TV displays.</p>
        <p>Registered approved sites: {{ $registeredCount }}</p>
        <p>Current Android TV app: v{{ $android['version_name'] }} ({{ $android['version_code'] }})</p>
        <a class="button" href="{{ route('masjids.register') }}">Register Masjid / Surau</a>
        <a class="button" href="{{ route('android.download') }}">Download Android TV App</a>
    </div>
</section>
</body>
</html>
