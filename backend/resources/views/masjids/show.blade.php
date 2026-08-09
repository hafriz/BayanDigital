<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $masjid->name }} | bayanDigital</title>
    <style>
        :root { color-scheme: dark; --navy:#061626; --emerald:#0f766e; --gold:#ffd166; --cream:#fff7e6; --muted:#b8d8d0; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; font-family:Inter,ui-sans-serif,system-ui,sans-serif; color:var(--cream); background:radial-gradient(circle at 12% 10%,rgba(255,209,102,.18),transparent 28%),linear-gradient(135deg,var(--navy),#082c27 65%,#020617); }
        .shell { width:min(1080px,calc(100% - 32px)); margin:auto; padding:28px 0 64px; }
        header { display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:42px; }
        .brand { color:inherit; text-decoration:none; font-size:23px; font-weight:650; letter-spacing:-.06em; }.brand b{color:var(--gold);font-weight:950}
        .id { color:var(--muted); font-weight:700; }
        .hero { text-align:center; margin-bottom:32px; }.hero h1{font-size:clamp(34px,7vw,66px);letter-spacing:-.05em;margin:10px 0}.hero p{color:var(--muted);font-size:18px}
        .eyebrow { color:var(--gold); text-transform:uppercase; letter-spacing:.16em; font-weight:900; font-size:13px; }
        .prayers { display:grid; grid-template-columns:repeat(6,1fr); gap:10px; margin-bottom:24px; }
        .prayer,.donation { border:1px solid rgba(255,255,255,.15); background:rgba(255,255,255,.08); backdrop-filter:blur(16px); box-shadow:0 20px 60px rgba(0,0,0,.22); }
        .prayer { border-radius:18px; padding:18px 10px; text-align:center; }.prayer span{display:block;color:var(--muted);font-size:12px;font-weight:900;text-transform:uppercase}.prayer strong{display:block;font-size:24px;margin-top:7px}
        .donation { display:grid; grid-template-columns:minmax(220px,340px) 1fr; gap:clamp(24px,6vw,64px); align-items:center; border-radius:32px; padding:clamp(24px,5vw,52px); }
        .qr { width:100%; max-width:340px; aspect-ratio:1; border-radius:22px; object-fit:contain; padding:12px; background:white; }
        .placeholder { display:grid; place-items:center; text-align:center; color:var(--navy); font-weight:950; line-height:1.5; }
        .donation h2 { font-size:clamp(30px,5vw,50px); letter-spacing:-.04em; margin:10px 0; }.caption{font-size:clamp(18px,2.5vw,24px);line-height:1.5}.account{display:inline-block;margin-top:12px;padding:12px 16px;border-radius:12px;background:rgba(255,209,102,.13);color:var(--gold);font-weight:850;overflow-wrap:anywhere}
        @media(max-width:760px){header{align-items:flex-start}.prayers{grid-template-columns:repeat(3,1fr)}.donation{grid-template-columns:1fr;text-align:center}.qr{margin:auto}.account{display:block}}
    </style>
</head>
<body><main class="shell">
    <header><a class="brand" href="{{ route('landing') }}" aria-label="bayanDigital home">bayan<b>Digital</b></a><span class="id">{{ $masjid->public_id }}</span></header>
    <section class="hero"><div class="eyebrow">{{ ucfirst($masjid->type) }} portal</div><h1>{{ $masjid->name }}</h1>@if($masjid->address)<p>{{ $masjid->address }}</p>@endif</section>
    <section class="prayers" aria-label="Today's prayer times">
        @foreach(['subuh' => 'Subuh', 'syuruk' => 'Syuruk', 'zohor' => 'Zohor', 'asar' => 'Asar', 'maghrib' => 'Maghrib', 'isyak' => 'Isyak'] as $key => $label)
            <div class="prayer"><span>{{ $label }}</span><strong>{{ $prayerTime->times[$key] ?? '—' }}</strong></div>
        @endforeach
    </section>
    <section class="donation" aria-labelledby="donation-heading">
        @if($donationQrUrl)
            <img class="qr" src="{{ $donationQrUrl }}" alt="Donation QR code for {{ $masjid->name }}">
        @else
            <div class="qr placeholder" role="img" aria-label="Donation QR code has not been configured">DONATION QR<br>NOT CONFIGURED</div>
        @endif
        <div><div class="eyebrow">Donations</div><h2 id="donation-heading">Support {{ $masjid->name }}</h2><p class="caption">{{ $masjid->donation_caption ?: 'Your contribution helps sustain our worship, education, and community programmes.' }}</p>@if($masjid->donation_account)<div class="account">{{ $masjid->donation_account }}</div>@endif</div>
    </section>
</main></body>
</html>
