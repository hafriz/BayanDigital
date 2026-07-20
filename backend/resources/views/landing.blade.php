<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masjid Smart Screen SaaS</title>
    <style>
        :root {
            --navy: #061626;
            --deep: #0b2f2a;
            --emerald: #0f766e;
            --mint: #b8f2e6;
            --gold: #ffd166;
            --cream: #fff7e6;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--cream);
            background:
                radial-gradient(circle at 15% 12%, rgba(255, 209, 102, .22), transparent 28%),
                radial-gradient(circle at 85% 18%, rgba(184, 242, 230, .16), transparent 30%),
                linear-gradient(135deg, var(--navy), #08201d 58%, #020617);
            overflow-x: hidden;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .12;
            background-image:
                linear-gradient(30deg, transparent 24%, rgba(255,255,255,.45) 25%, rgba(255,255,255,.45) 26%, transparent 27%, transparent 74%, rgba(255,255,255,.45) 75%, rgba(255,255,255,.45) 76%, transparent 77%),
                linear-gradient(150deg, transparent 24%, rgba(255,255,255,.45) 25%, rgba(255,255,255,.45) 26%, transparent 27%, transparent 74%, rgba(255,255,255,.45) 75%, rgba(255,255,255,.45) 76%, transparent 77%);
            background-size: 84px 84px;
        }
        .shell { width: min(1180px, calc(100% - 40px)); margin: 0 auto; position: relative; z-index: 1; }
        header { display: flex; align-items: center; justify-content: space-between; padding: 28px 0; }
        .brand { display: flex; align-items: center; gap: 14px; font-weight: 900; letter-spacing: .02em; }
        .logo {
            width: 52px; height: 52px; display: grid; place-items: center; border-radius: 18px;
            color: var(--navy); background: linear-gradient(145deg, var(--gold), #fff0a8);
            box-shadow: 0 16px 42px rgba(255, 209, 102, .28);
        }
        .nav a { color: var(--cream); text-decoration: none; margin-left: 22px; opacity: .88; font-weight: 700; }
        .hero { display: grid; grid-template-columns: 1.05fr .95fr; gap: 42px; align-items: center; min-height: calc(100vh - 110px); padding: 42px 0 72px; }
        .eyebrow { color: var(--gold); font-weight: 900; text-transform: uppercase; letter-spacing: .18em; }
        h1 { font-size: clamp(48px, 7vw, 86px); line-height: .95; margin: 18px 0; letter-spacing: -.06em; }
        .lead { color: #d8fff5; font-size: clamp(18px, 2.3vw, 24px); line-height: 1.65; max-width: 680px; }
        .actions { display: flex; flex-wrap: wrap; gap: 16px; margin: 34px 0; }
        .button {
            display: inline-flex; align-items: center; justify-content: center; gap: 10px; border-radius: 999px;
            padding: 16px 24px; font-weight: 900; text-decoration: none; transition: transform .2s ease, box-shadow .2s ease;
        }
        .button:hover { transform: translateY(-2px); }
        .button.primary { color: var(--navy); background: var(--gold); box-shadow: 0 18px 44px rgba(255, 209, 102, .28); }
        .button.secondary { color: var(--cream); border: 1px solid rgba(255,255,255,.22); background: rgba(255,255,255,.08); backdrop-filter: blur(12px); }
        .stats { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 14px; margin-top: 32px; }
        .stat, .screen-card, .feature { border: 1px solid rgba(255,255,255,.16); background: rgba(255,255,255,.08); box-shadow: 0 24px 80px rgba(0,0,0,.28); backdrop-filter: blur(18px); }
        .stat { border-radius: 24px; padding: 18px; }
        .stat strong { display: block; color: var(--gold); font-size: 32px; }
        .screen-card { border-radius: 34px; padding: 22px; transform: rotate(1deg); }
        .screen { overflow: hidden; border-radius: 24px; background: #071a2d; min-height: 390px; border: 7px solid #111827; box-shadow: inset 0 0 0 1px rgba(255,255,255,.08); }
        .screen-top { padding: 26px; text-align: center; background: linear-gradient(135deg, rgba(15,118,110,.68), rgba(6,22,38,.95)); }
        .screen-time { color: var(--gold); font-size: 58px; line-height: 1; font-weight: 950; }
        .screen-name { font-size: 24px; font-weight: 900; margin-bottom: 10px; }
        .prayer-grid { display: grid; grid-template-columns: repeat(5,1fr); gap: 8px; padding: 16px; }
        .prayer { text-align: center; border-radius: 16px; padding: 12px 8px; background: rgba(184,242,230,.1); }
        .prayer span { display: block; color: var(--mint); font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .prayer b { display: block; font-size: 20px; margin-top: 6px; }
        .ticker { margin: 16px; border-radius: 999px; padding: 12px 18px; color: var(--navy); background: linear-gradient(90deg, var(--gold), #ffe8a3); font-weight: 900; white-space: nowrap; overflow: hidden; }
        .features { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 18px; padding-bottom: 72px; }
        .feature { border-radius: 28px; padding: 24px; }
        .feature h3 { margin: 0 0 10px; color: var(--gold); }
        .feature p { margin: 0; color: #d8fff5; line-height: 1.6; }
        @media (max-width: 860px) {
            header, .hero { grid-template-columns: 1fr; }
            .hero { min-height: auto; }
            .nav { display: none; }
            .stats, .features { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="shell">
    <header>
        <div class="brand"><div class="logo">☪</div><span>Masjid Smart Screen</span></div>
        <nav class="nav">
            <a href="{{ route('masjids.register') }}">Register</a>
            <a href="{{ route('android.download') }}">Android App</a>
        </nav>
    </header>

    <main class="hero">
        <section>
            <div class="eyebrow">SaaS Paparan Pintar Masjid & Surau</div>
            <h1>Prayer times, announcements, and iqamah alerts on one beautiful TV screen.</h1>
            <p class="lead">A Malaysian-focused smart display platform with cached official JAKIM e-Solat prayer times, tenant-based content management, Android TV pairing, running text, media slides, Azan alerts, Iqamah countdowns, and silent prayer mode.</p>
            <div class="actions">
                <a class="button primary" href="{{ route('masjids.register') }}">Register Masjid / Surau →</a>
                <a class="button secondary" href="{{ route('android.download') }}">Download Android TV v{{ $android['version_name'] }}</a>
            </div>
            <div class="stats">
                <div class="stat"><strong>{{ $registeredCount }}</strong><span>approved sites</span></div>
                <div class="stat"><strong>6h</strong><span>automatic TV sync</span></div>
                <div class="stat"><strong>24/7</strong><span>offline-first display</span></div>
            </div>
        </section>

        <aside class="screen-card" aria-label="Android TV smart screen preview">
            <div class="screen">
                <div class="screen-top">
                    <div class="screen-name">Masjid Al-Falah</div>
                    <div class="screen-time">07:24</div>
                    <div>SGR01 • 20 Jul 2026 • 5 Safar 1448H</div>
                </div>
                <div class="prayer-grid">
                    <div class="prayer"><span>Subuh</span><b>05:47</b></div>
                    <div class="prayer"><span>Zohor</span><b>13:18</b></div>
                    <div class="prayer"><span>Asar</span><b>16:42</b></div>
                    <div class="prayer"><span>Maghrib</span><b>19:29</b></div>
                    <div class="prayer"><span>Isyak</span><b>20:42</b></div>
                </div>
                <div class="ticker">Tabung Jumaat: RM 4,250 • Kuliah Maghrib malam ini • Sila luruskan saf</div>
            </div>
        </aside>
    </main>

    <section class="features">
        <article class="feature"><h3>Tenant-based setup</h3><p>Each masjid or surau receives a unique ID so every Android TV loads the correct local settings, timetable, and content.</p></article>
        <article class="feature"><h3>JAKIM cached data</h3><p>Prayer times are cached in Laravel monthly to reduce downtime risk and keep displays resilient when external services are unavailable.</p></article>
        <article class="feature"><h3>Made for worship spaces</h3><p>Large typography, Azan states, Iqamah countdowns, and silent mode keep the display useful without distracting congregants.</p></article>
    </section>
</div>
</body>
</html>
