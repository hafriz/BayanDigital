<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071f1d">
    <title>Register Masjid / Surau | bayanDigital</title>
    <style>
        :root {
            --navy: #061626;
            --deep: #071f1d;
            --emerald: #0f766e;
            --mint: #b8f2e6;
            --gold: #ffd166;
            --cream: #fffaf0;
            --ink: #14312e;
            --muted: #607572;
            --danger: #b42318;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--cream);
            background:
                radial-gradient(circle at 8% 8%, rgba(255, 209, 102, .2), transparent 24rem),
                radial-gradient(circle at 90% 24%, rgba(184, 242, 230, .12), transparent 26rem),
                linear-gradient(135deg, var(--navy), var(--deep) 58%, #020617);
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .1;
            background-image:
                linear-gradient(30deg, transparent 24%, rgba(255,255,255,.55) 25%, rgba(255,255,255,.55) 26%, transparent 27%, transparent 74%, rgba(255,255,255,.55) 75%, rgba(255,255,255,.55) 76%, transparent 77%),
                linear-gradient(150deg, transparent 24%, rgba(255,255,255,.55) 25%, rgba(255,255,255,.55) 26%, transparent 27%, transparent 74%, rgba(255,255,255,.55) 75%, rgba(255,255,255,.55) 76%, transparent 77%);
            background-size: 84px 84px;
        }
        a { color: inherit; }
        .shell { width: min(1120px, calc(100% - 40px)); margin: 0 auto; position: relative; z-index: 1; }
        header { display: flex; align-items: center; justify-content: space-between; padding: 26px 0; }
        .brand { display: flex; align-items: center; font-weight: 900; text-decoration: none; }
        .brand-name { font-size: 25px; font-weight: 650; letter-spacing: -.065em; line-height: 1; }
        .brand-name b { color: var(--gold); font-weight: 950; }
        .back-link { color: #d8fff5; font-size: 14px; font-weight: 800; text-decoration: none; }
        .back-link:hover { color: var(--gold); }
        main { display: grid; grid-template-columns: .78fr 1.22fr; gap: 38px; align-items: start; padding: 28px 0 72px; }
        .intro { position: sticky; top: 28px; padding: 42px 8px 20px 0; }
        .eyebrow { color: var(--gold); font-size: 13px; font-weight: 900; letter-spacing: .18em; text-transform: uppercase; }
        h1 { margin: 18px 0; max-width: 520px; font-size: clamp(42px, 5vw, 66px); line-height: 1; letter-spacing: -.055em; }
        .lead { color: #d8fff5; line-height: 1.7; font-size: 17px; }
        .steps { display: grid; gap: 16px; margin: 34px 0 0; padding: 0; list-style: none; counter-reset: steps; }
        .steps li { display: grid; grid-template-columns: 38px 1fr; gap: 13px; align-items: center; color: #d8fff5; }
        .steps li::before {
            counter-increment: steps; content: counter(steps); width: 38px; height: 38px; display: grid; place-items: center;
            border: 1px solid rgba(255,209,102,.4); border-radius: 50%; color: var(--gold); font-weight: 900;
            background: rgba(255,255,255,.06);
        }
        .form-card {
            overflow: hidden; border: 1px solid rgba(255,255,255,.2); border-radius: 30px; color: var(--ink);
            background: rgba(255,250,240,.98); box-shadow: 0 30px 90px rgba(0,0,0,.35);
        }
        .card-top { padding: 28px 32px; color: var(--cream); background: linear-gradient(120deg, #0f766e, #0b4741); }
        .card-top h2 { margin: 0 0 7px; font-size: 24px; }
        .card-top p { margin: 0; color: #d8fff5; font-size: 14px; }
        form { padding: 30px 32px 34px; }
        .error-summary { margin: 0 0 24px; padding: 16px 18px; border: 1px solid #f5b7b1; border-radius: 14px; color: #7a271a; background: #fff1f0; }
        .error-summary strong { display: block; margin-bottom: 5px; }
        .error-summary ul { margin: 5px 0 0; padding-left: 20px; }
        fieldset { margin: 0 0 28px; padding: 0; border: 0; }
        legend { width: 100%; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1px solid #dbe7e3; color: #0b4741; font-size: 13px; font-weight: 900; letter-spacing: .12em; text-transform: uppercase; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .field { display: grid; gap: 8px; }
        .field.full { grid-column: 1 / -1; }
        label { font-size: 14px; font-weight: 800; }
        .required { color: var(--danger); }
        input, select, textarea {
            width: 100%; border: 1px solid #bfd0cb; border-radius: 12px; padding: 13px 14px; color: var(--ink);
            background: #fff; font: inherit; outline: none; transition: border-color .2s, box-shadow .2s;
        }
        textarea { min-height: 104px; resize: vertical; }
        input:focus, select:focus, textarea:focus { border-color: var(--emerald); box-shadow: 0 0 0 4px rgba(15,118,110,.12); }
        [aria-invalid="true"] { border-color: var(--danger); }
        .hint { margin: 0; color: var(--muted); font-size: 12px; line-height: 1.5; }
        .field-error { margin: 0; color: var(--danger); font-size: 12px; font-weight: 700; }
        .zone-picker { display: grid; gap: 9px; }
        .zone-search-wrap { position: relative; }
        .zone-search-wrap::before { content: "⌕"; position: absolute; left: 14px; top: 50%; transform: translateY(-52%); color: var(--emerald); font-size: 20px; pointer-events: none; }
        .zone-search-wrap input { padding-left: 42px; background: #f5faf8; }
        .zone-status { min-height: 18px; margin: 0; color: var(--muted); font-size: 12px; }
        .submit-row { display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .privacy { margin: 0; max-width: 300px; color: var(--muted); font-size: 12px; line-height: 1.5; }
        button {
            flex: 0 0 auto; border: 0; border-radius: 999px; padding: 15px 22px; color: var(--navy); background: var(--gold);
            box-shadow: 0 14px 28px rgba(204,151,29,.22); font: inherit; font-weight: 900; cursor: pointer; transition: transform .2s, box-shadow .2s;
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 18px 34px rgba(204,151,29,.3); }
        button:focus-visible, a:focus-visible { outline: 3px solid var(--gold); outline-offset: 4px; }
        @media (max-width: 820px) {
            main { grid-template-columns: 1fr; padding-top: 5px; }
            .intro { position: static; padding-top: 20px; }
            .steps { grid-template-columns: repeat(3, 1fr); }
            .steps li { grid-template-columns: 1fr; align-items: start; font-size: 13px; }
        }
        @media (max-width: 600px) {
            .shell { width: min(100% - 24px, 1120px); }
            header { padding: 18px 0; }
            h1 { font-size: 42px; }
            .steps { grid-template-columns: 1fr; }
            .steps li { grid-template-columns: 38px 1fr; }
            .card-top, form { padding: 24px 20px; }
            .grid { grid-template-columns: 1fr; }
            .field.full { grid-column: auto; }
            .submit-row { align-items: stretch; flex-direction: column; }
            button { width: 100%; }
        }
    </style>
</head>
<body>
<div class="shell">
    <header>
        <a class="brand" href="{{ route('landing') }}" aria-label="bayanDigital home">
            <span class="brand-name">bayan<b>Digital</b></span>
        </a>
        <a class="back-link" href="{{ route('landing') }}">← Back to home</a>
    </header>

    <main>
        <section class="intro" aria-labelledby="page-title">
            <div class="eyebrow">Start your smart display</div>
            <h1 id="page-title">Bring your masjid screen to life.</h1>
            <p class="lead">Register your masjid or surau to receive a unique Android TV setup ID and manage prayer times, announcements, and display content in one place.</p>
            <ol class="steps">
                <li>Share your organisation details</li>
                <li>Wait for administrator approval</li>
                <li>Pair your Android TV with the unique ID</li>
            </ol>
        </section>

        <section class="form-card" aria-labelledby="form-title">
            <div class="card-top">
                <h2 id="form-title">Registration details</h2>
                <p>Fields marked with <span aria-hidden="true">*</span> are required.</p>
            </div>

            <form method="post" action="{{ route('masjids.store') }}">
                @csrf

                @if ($errors->any())
                    <div class="error-summary" role="alert">
                        <strong>Please check the information below.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <fieldset>
                    <legend>Masjid / Surau information</legend>
                    <div class="grid">
                        <div class="field full">
                            <label for="name">Organisation name <span class="required" aria-hidden="true">*</span></label>
                            <input id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Masjid Al-Falah" autocomplete="organization" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                            @error('name')<p class="field-error" id="name-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="type">Organisation type <span class="required" aria-hidden="true">*</span></label>
                            <select id="type" name="type" required @error('type') aria-invalid="true" aria-describedby="type-error" @enderror>
                                <option value="masjid" @selected(old('type', 'masjid') === 'masjid')>Masjid</option>
                                <option value="surau" @selected(old('type') === 'surau')>Surau</option>
                            </select>
                            @error('type')<p class="field-error" id="type-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="zone_code">JAKIM zone code <span class="required" aria-hidden="true">*</span></label>
                            <div class="zone-picker">
                                <div class="zone-search-wrap">
                                    <input id="zone_search" type="search" placeholder="Search district, state, or code…" autocomplete="off" aria-controls="zone_code" aria-describedby="zone-help zone-status">
                                </div>
                                <select id="zone_code" name="zone_code" required @error('zone_code') aria-invalid="true" aria-describedby="zone-help zone-error" @else aria-describedby="zone-help" @enderror>
                                    <option value="">Select your prayer-time zone</option>
                                    @foreach ($jakimZones as $state => $zones)
                                        <optgroup label="{{ $state }}">
                                            @foreach ($zones as $code => $districts)
                                                <option value="{{ $code }}" data-search="{{ strtolower($state.' '.$code.' '.$districts) }}" @selected(old('zone_code') === $code)>{{ $code }} — {{ $districts }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <p class="zone-status" id="zone-status" aria-live="polite"></p>
                            </div>
                            <p class="hint" id="zone-help">Search for your district, then select its official JAKIM zone.</p>
                            @error('zone_code')<p class="field-error" id="zone-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field full">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" placeholder="Full address of the masjid or surau" autocomplete="street-address" @error('address') aria-invalid="true" aria-describedby="address-error" @enderror>{{ old('address') }}</textarea>
                            @error('address')<p class="field-error" id="address-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Primary contact</legend>
                    <div class="grid">
                        <div class="field full">
                            <label for="contact_name">Contact name <span class="required" aria-hidden="true">*</span></label>
                            <input id="contact_name" name="contact_name" value="{{ old('contact_name') }}" placeholder="Name of person in charge" autocomplete="name" required @error('contact_name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>
                            @error('contact_name')<p class="field-error" id="contact-name-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="contact_phone">Phone number <span class="required" aria-hidden="true">*</span></label>
                            <input id="contact_phone" name="contact_phone" type="tel" value="{{ old('contact_phone') }}" placeholder="e.g. 012-345 6789" autocomplete="tel" required @error('contact_phone') aria-invalid="true" aria-describedby="contact-phone-error" @enderror>
                            @error('contact_phone')<p class="field-error" id="contact-phone-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="contact_email">Email address</label>
                            <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email') }}" placeholder="admin@example.com" autocomplete="email" @error('contact_email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>
                            @error('contact_email')<p class="field-error" id="contact-email-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </fieldset>

                <div class="submit-row">
                    <p class="privacy">Your details are used only to review the registration and configure your smart display.</p>
                    <button type="submit">Submit registration →</button>
                </div>
            </form>
        </section>
    </main>
</div>
<script>
    (() => {
        const search = document.querySelector('#zone_search');
        const select = document.querySelector('#zone_code');
        const status = document.querySelector('#zone-status');
        const groups = [...select.querySelectorAll('optgroup')];

        const filterZones = () => {
            const query = search.value.trim().toLocaleLowerCase('ms');
            let matches = 0;

            groups.forEach((group) => {
                let groupMatches = 0;
                [...group.querySelectorAll('option')].forEach((option) => {
                    const matchesQuery = !query || option.dataset.search.includes(query);
                    option.hidden = !matchesQuery;
                    if (matchesQuery) groupMatches++;
                });
                group.hidden = groupMatches === 0;
                matches += groupMatches;
            });

            status.textContent = query
                ? `${matches} zone${matches === 1 ? '' : 's'} found`
                : '';
        };

        search.addEventListener('input', filterZones);
        search.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') return;
            event.preventDefault();
            const visibleOptions = [...select.options].filter((option) => option.value && !option.hidden);
            if (visibleOptions.length === 1) {
                select.value = visibleOptions[0].value;
                select.focus();
            }
        });
    })();
</script>
</body>
</html>
