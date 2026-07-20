<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | bayanDigital</title>
    <style>
        :root { --navy:#071827; --deep:#0b2f2a; --emerald:#0f766e; --gold:#f4c95d; --cream:#fffaf0; --ink:#16302d; --muted:#687b78; --line:#dce8e4; --danger:#b42318; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--ink); background:#f3f7f5; font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif; }
        a { color:inherit; }
        button,input,select,textarea { font:inherit; }
        .app { min-height:100vh; display:grid; grid-template-columns:260px minmax(0,1fr); }
        .sidebar { color:var(--cream); padding:30px 22px; background:radial-gradient(circle at 20% 4%,rgba(244,201,93,.18),transparent 25%),linear-gradient(160deg,var(--navy),var(--deep)); position:sticky; top:0; height:100vh; }
        .brand { display:inline-block; margin:0 10px 38px; color:white; font-size:27px; font-weight:650; letter-spacing:-.065em; text-decoration:none; }
        .brand b { color:var(--gold); font-weight:950; }
        .nav-label { margin:20px 12px 8px; color:#8fb2aa; font-size:11px; font-weight:900; letter-spacing:.14em; text-transform:uppercase; }
        .nav-link { display:flex; align-items:center; gap:12px; margin:5px 0; padding:12px 14px; border-radius:14px; color:#d8eee8; font-weight:750; text-decoration:none; }
        .nav-link:hover,.nav-link.active { color:white; background:rgba(255,255,255,.1); }
        .nav-link.active { box-shadow:inset 3px 0 var(--gold); }
        .nav-icon { width:24px; text-align:center; color:var(--gold); }
        .sidebar-user { position:absolute; left:22px; right:22px; bottom:24px; padding:16px; border:1px solid rgba(255,255,255,.12); border-radius:16px; background:rgba(255,255,255,.06); }
        .sidebar-user strong,.sidebar-user small { display:block; overflow:hidden; text-overflow:ellipsis; }
        .sidebar-user small { margin:4px 0 12px; color:#a9c7c0; }
        .logout { border:0; padding:0; color:var(--gold); background:none; font-weight:800; cursor:pointer; }
        .main { min-width:0; padding:38px clamp(22px,4vw,58px) 70px; }
        .topbar { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; margin-bottom:28px; }
        .eyebrow { color:var(--emerald); font-size:12px; font-weight:900; letter-spacing:.14em; text-transform:uppercase; }
        h1 { margin:6px 0 0; color:var(--navy); font-size:clamp(30px,4vw,44px); line-height:1; letter-spacing:-.045em; }
        h2 { margin:0; color:var(--navy); font-size:21px; letter-spacing:-.025em; }
        .subtitle { margin:10px 0 0; color:var(--muted); }
        .button { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:44px; border:1px solid transparent; border-radius:12px; padding:10px 16px; color:white; background:var(--emerald); font-weight:850; text-decoration:none; cursor:pointer; }
        .button:hover { filter:brightness(.95); transform:translateY(-1px); }
        .button.secondary { color:var(--ink); border-color:var(--line); background:white; }
        .button.danger { color:var(--danger); border-color:#f4c7c3; background:#fff5f4; }
        .button.small { min-height:34px; padding:7px 11px; border-radius:9px; font-size:13px; }
        .alert { margin-bottom:22px; padding:15px 18px; border-radius:14px; font-weight:700; }
        .alert.success { color:#075e54; border:1px solid #a7ded3; background:#e8faf5; }
        .alert.error { color:#8b1e16; border:1px solid #f4c7c3; background:#fff2f0; }
        .alert ul { margin:8px 0 0; padding-left:20px; }
        .stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:26px; }
        .category-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:20px; }
        .stat { padding:23px; border:1px solid var(--line); border-radius:19px; background:white; box-shadow:0 12px 35px rgba(7,24,39,.05); }
        .stat span { color:var(--muted); font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
        .stat strong { display:block; margin-top:7px; color:var(--navy); font-size:34px; }
        .panel { overflow:hidden; border:1px solid var(--line); border-radius:19px; background:white; box-shadow:0 12px 35px rgba(7,24,39,.04); }
        .panel-head { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:20px 22px; border-bottom:1px solid var(--line); }
        .panel-body { padding:22px; }
        .table-wrap { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; }
        th,td { padding:15px 18px; border-bottom:1px solid #edf2f0; text-align:left; vertical-align:middle; }
        th { color:var(--muted); background:#fbfdfc; font-size:11px; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
        tbody tr:last-child td { border-bottom:0; }
        .primary-text { color:var(--navy); font-weight:850; }
        .secondary-text { margin-top:3px; color:var(--muted); font-size:13px; }
        .badge { display:inline-flex; border-radius:999px; padding:5px 9px; color:#435954; background:#edf3f1; font-size:11px; font-weight:900; letter-spacing:.04em; text-transform:uppercase; }
        .badge.approved,.badge.active,.badge.admin { color:#08665b; background:#dff7f1; }
        .badge.pending { color:#8a5d00; background:#fff2c8; }
        .badge.suspended,.badge.rejected,.badge.inactive { color:#a22a21; background:#ffebe9; }
        .actions-inline { display:flex; align-items:center; flex-wrap:wrap; gap:7px; }
        .filters { display:grid; grid-template-columns:minmax(220px,1fr) 190px auto; gap:10px; }
        .field { margin-bottom:19px; }
        .field label { display:block; margin-bottom:7px; color:var(--navy); font-size:13px; font-weight:850; }
        .field small { display:block; margin-top:6px; color:var(--muted); }
        input,select,textarea { width:100%; border:1px solid #cadbd6; border-radius:11px; padding:11px 13px; color:var(--ink); background:white; outline:none; }
        input:focus,select:focus,textarea:focus { border-color:var(--emerald); box-shadow:0 0 0 3px rgba(15,118,110,.12); }
        textarea { min-height:115px; resize:vertical; }
        .form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0 18px; }
        .field.full { grid-column:1/-1; }
        .form-actions { display:flex; gap:10px; padding-top:5px; }
        .empty { padding:46px 22px; color:var(--muted); text-align:center; }
        .pager { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 20px; border-top:1px solid var(--line); color:var(--muted); font-size:13px; }
        @media(max-width:960px) { .app{display:block}.sidebar{position:static;height:auto;padding:20px}.brand{margin-bottom:18px}.sidebar nav{display:flex;gap:5px;overflow-x:auto}.nav-label{display:none}.nav-link{white-space:nowrap}.sidebar-user{position:static;margin-top:16px}.stats,.category-grid{grid-template-columns:repeat(2,1fr)} }
        @media(max-width:640px) { .main{padding:28px 16px 50px}.topbar{display:block}.topbar .button{margin-top:18px}.stats,.category-grid,.form-grid,.filters{grid-template-columns:1fr}.stat{padding:18px}th,td{padding:13px}.hide-mobile{display:none} }
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <a class="brand" href="{{ route('admin.dashboard') }}">bayan<b>Digital</b></a>
        <nav aria-label="Admin navigation">
            <div class="nav-label">Workspace</div>
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span class="nav-icon">▦</span>Overview</a>
            <a class="nav-link {{ request()->routeIs('admin.masjids.*') ? 'active' : '' }}" href="{{ route('admin.masjids.index') }}"><span class="nav-icon">⌂</span>Masjids</a>
            @if(auth()->user()->isAdmin())
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><span class="nav-icon">◎</span>Users</a>
                <a class="nav-link {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" href="{{ route('admin.backups.index') }}"><span class="nav-icon">☁</span>Backups</a>
            @endif
            <div class="nav-label">Public</div>
            <a class="nav-link" href="{{ route('landing') }}" target="_blank"><span class="nav-icon">↗</span>View website</a>
        </nav>
        <div class="sidebar-user">
            <strong>{{ auth()->user()->name }}</strong>
            <small>{{ ucfirst(auth()->user()->role) }} · {{ auth()->user()->email }}</small>
            <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="logout" type="submit">Sign out →</button></form>
        </div>
    </aside>
    <main class="main">
        <header class="topbar">
            <div><div class="eyebrow">Management console</div><h1>@yield('title', 'Dashboard')</h1>@hasSection('subtitle')<p class="subtitle">@yield('subtitle')</p>@endif</div>
            @yield('top-action')
        </header>
        @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert error"><strong>Please check the information below.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
</body>
</html>
