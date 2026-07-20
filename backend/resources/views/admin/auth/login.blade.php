<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | bayanDigital</title>
    <style>
        :root{--navy:#071827;--deep:#0b2f2a;--emerald:#0f766e;--gold:#f4c95d;--cream:#fffaf0}*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;color:#17302d;background:radial-gradient(circle at 18% 15%,rgba(244,201,93,.2),transparent 25%),linear-gradient(145deg,var(--navy),var(--deep));font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}.card{width:min(440px,100%);padding:38px;border:1px solid rgba(255,255,255,.18);border-radius:26px;background:rgba(255,255,255,.97);box-shadow:0 35px 90px rgba(0,0,0,.32)}.brand{display:inline-block;margin-bottom:35px;color:var(--navy);font-size:30px;font-weight:650;letter-spacing:-.065em;text-decoration:none}.brand b{color:var(--emerald);font-weight:950}h1{margin:0;font-size:34px;letter-spacing:-.04em}p{margin:9px 0 26px;color:#687b78;line-height:1.55}.field{margin:16px 0}.field label{display:block;margin-bottom:7px;font-size:13px;font-weight:850}input[type=email],input[type=password]{width:100%;border:1px solid #cadbd6;border-radius:12px;padding:13px;outline:none;font:inherit}input:focus{border-color:var(--emerald);box-shadow:0 0 0 3px rgba(15,118,110,.12)}.remember{display:flex;align-items:center;gap:8px;margin:17px 0;color:#526762;font-size:14px}.remember input{width:17px;height:17px}.button{width:100%;border:0;border-radius:12px;padding:14px;color:white;background:var(--emerald);font:800 15px inherit;cursor:pointer}.error{margin-bottom:20px;padding:13px;border-radius:11px;color:#96251c;background:#ffebe9;font-size:14px}.back{display:block;margin-top:20px;color:#526762;text-align:center;font-size:14px;text-decoration:none}@media(max-width:520px){.card{padding:28px 22px}}
    </style>
</head>
<body>
<main class="card">
    <a class="brand" href="{{ route('landing') }}">bayan<b>Digital</b></a>
    <h1>Welcome back</h1>
    <p>Sign in to manage masjids, screen content, and user access.</p>
    @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.login.store') }}">
        @csrf
        <div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus></div>
        <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
        <label class="remember"><input name="remember" type="checkbox" value="1"> Keep me signed in</label>
        <button class="button" type="submit">Sign in to console</button>
    </form>
    <a class="back" href="{{ route('landing') }}">← Return to website</a>
</main>
</body>
</html>
