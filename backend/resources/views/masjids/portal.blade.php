<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $masjid->name }} | bayanDigital</title>
    <style>
        :root{--navy:#071827;--emerald:#0f766e;--gold:#f4c95d;--cream:#fffaf0;--muted:#61736f}*{box-sizing:border-box}body{margin:0;color:var(--navy);background:#f4f8f6;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.hero{padding:70px 20px;color:white;text-align:center;background:radial-gradient(circle at top,rgba(244,201,93,.22),transparent 35%),linear-gradient(145deg,var(--navy),#0b443b)}.hero span{color:var(--gold);font-size:12px;font-weight:900;letter-spacing:.18em;text-transform:uppercase}.hero h1{margin:12px 0 8px;font-size:clamp(36px,7vw,64px);letter-spacing:-.05em}.hero p{margin:0;color:#cbe2dc}.container{width:min(1100px,calc(100% - 32px));margin:0 auto;padding:58px 0 80px}.heading{text-align:center;margin-bottom:30px}.heading h2{margin:0 0 8px;font-size:clamp(27px,4vw,39px);letter-spacing:-.04em}.heading p{margin:0;color:var(--muted)}.members{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px}.member{padding:28px 22px;border:1px solid #dce8e4;border-radius:22px;background:white;text-align:center;box-shadow:0 15px 40px rgba(7,24,39,.06)}.photo,.initials{width:112px;height:112px;margin:0 auto 18px;border-radius:50%}.photo{object-fit:cover}.initials{display:grid;place-items:center;color:white;background:linear-gradient(145deg,var(--emerald),#0b443b);font-size:34px;font-weight:900}.member h3{margin:0;font-size:20px}.position{margin:6px 0 16px;color:var(--emerald);font-weight:800}.contact{display:flex;justify-content:center;flex-wrap:wrap;gap:8px}.contact a{padding:7px 10px;border-radius:9px;color:var(--navy);background:#eef5f2;font-size:13px;text-decoration:none}.empty{text-align:center;color:var(--muted)}footer{padding:24px;color:#9ab4ae;background:var(--navy);text-align:center}@media(max-width:800px){.members{grid-template-columns:repeat(2,1fr)}}@media(max-width:520px){.members{grid-template-columns:1fr}.hero{padding:52px 18px}}
    </style>
</head>
<body>
<header class="hero"><span>{{ ucfirst($masjid->type) }}</span><h1>{{ $masjid->name }}</h1>@if($masjid->address)<p>{{ $masjid->address }}</p>@endif</header>
<main class="container"><section aria-labelledby="committee-heading"><div class="heading"><h2 id="committee-heading">Ahli Jawatankuasa Masjid</h2><p>Barisan kepimpinan yang berkhidmat untuk komuniti.</p></div>
    @if($committeeMembers->isEmpty())<p class="empty">Maklumat ahli jawatankuasa akan dikemas kini tidak lama lagi.</p>@else<div class="members">
    @foreach($committeeMembers as $member)<article class="member">
        @if($member->photo_path)<img class="photo" src="{{ Storage::url($member->photo_path) }}" alt="Foto {{ $member->name }}">@else<div class="initials" aria-hidden="true">{{ Str::upper(Str::substr($member->name, 0, 1)) }}</div>@endif
        <h3>{{ $member->name }}</h3><p class="position">{{ $member->position }}</p>
        @if(($member->show_phone_publicly && $member->phone) || ($member->show_email_publicly && $member->email))<div class="contact">
            @if($member->show_phone_publicly && $member->phone)<a href="tel:{{ preg_replace('/[^+0-9]/', '', $member->phone) }}">{{ $member->phone }}</a>@endif
            @if($member->show_email_publicly && $member->email)<a href="mailto:{{ $member->email }}">{{ $member->email }}</a>@endif
        </div>@endif
    </article>@endforeach
    </div>@endif
</section></main><footer>Powered by bayanDigital</footer>
</body></html>
