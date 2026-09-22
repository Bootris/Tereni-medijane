@props(['title' => null, 'description' => null, 'head' => null])

@php
    $siteName = $site['site_name'] ?? config('app.name');
    $pageTitle = $title ? "{$title} — Tereni Medijana" : 'Tereni Medijana';
@endphp
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:type" content="website">
    <link rel="icon" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:wght@500;600;700&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        {{-- "Asfalt i farba": the palette of the courts themselves — asphalt,
             painted lines, court green, marking yellow. Yellow is rationed:
             focus rings, the open-reports number, the report form stripe. --}}
        :root {
            --asfalt: #262b34;
            --asfalt-2: #323a47;
            --teren: #2e7a4a;
            --teren-dark: #23603a;
            --linija: #f5f2e8;
            --signal: #f0b429;
            --crveno: #cd4034;
            --ink: #22262e;
            --muted: #68707d;
            --line: #dde1d6;
            --bg: #eef0eb;
            --card: #ffffff;
            /* Legacy aliases — page styles written against the old names. */
            --brand: var(--teren);
            --brand-dark: var(--teren-dark);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Barlow', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: var(--ink);
            background: var(--bg);
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }
        a { color: var(--teren-dark); }
        .wrap { max-width: 980px; margin: 0 auto; padding: 0 1rem; }
        :focus-visible { outline: 3px solid var(--signal); outline-offset: 2px; }

        /* Type roles: Anton = poster display, Barlow Condensed = signage labels. */
        .display { font-family: 'Anton', 'Barlow Condensed', sans-serif; font-weight: 400;
            text-transform: uppercase; letter-spacing: .02em; line-height: 1.08; }
        .eyebrow { font-family: 'Barlow Condensed', sans-serif; font-weight: 700; font-size: .82rem;
            letter-spacing: .16em; text-transform: uppercase; color: var(--teren); }
        .section-title { font-family: 'Barlow Condensed', sans-serif; font-weight: 700;
            font-size: 1.32rem; letter-spacing: .05em; text-transform: uppercase; line-height: 1.2; }
        /* A short strip of paint under a heading — the court-line motif. */
        .rule::after { content: ''; display: block; width: 2.7rem; height: .28rem;
            border-radius: 999px; background: var(--teren); margin-top: .4rem; }

        header.site { background: var(--asfalt); color: var(--linija);
            border-bottom: 3px solid var(--teren); }
        header.site .wrap {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            padding-top: .95rem; padding-bottom: .95rem;
        }
        header.site a { color: inherit; text-decoration: none; }
        .brand { display: flex; align-items: center; gap: .65rem; }
        .brand-mark { flex: 0 0 auto; display: block; color: var(--signal); }
        .brand-text { font-family: 'Barlow Condensed', sans-serif; font-weight: 700;
            font-size: 1.28rem; line-height: 1; letter-spacing: .13em; text-transform: uppercase; }
        .brand-text small { display: block; font-weight: 600; font-size: .66rem;
            letter-spacing: .22em; opacity: .62; margin-top: .3rem; }
        nav.top { display: flex; gap: 1.4rem; flex: 0 0 auto; }
        nav.top a { position: relative; font-family: 'Barlow Condensed', sans-serif; font-weight: 600;
            font-size: .95rem; letter-spacing: .1em; text-transform: uppercase; opacity: .8;
            white-space: nowrap; padding: .15rem 0; }
        nav.top a:hover { opacity: 1; }
        nav.top a.active { opacity: 1; }
        nav.top a.active::after { content: ''; position: absolute; left: 0; right: 0;
            bottom: -.28rem; height: 3px; border-radius: 999px; background: var(--signal); }

        main { padding: 1.6rem 0 3rem; }
        .card { background: var(--card); border: 1px solid var(--line); border-radius: .8rem; }

        /* Court cards + filter chips — shared by the map and the directory page. */
        .court-grid { list-style:none; padding:0; margin:0; display:grid; gap:.8rem;
            grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); }
        .court-card { padding:0; cursor:pointer; overflow:hidden; display:flex; flex-direction:column;
            transition:transform .15s, box-shadow .15s, border-color .15s; }
        .court-card:hover { transform:translateY(-2px); border-color:var(--teren);
            box-shadow:0 8px 20px rgba(38,43,52,.12); }
        .court-card[data-issue="1"] { border-top:4px solid var(--crveno); }
        .court-card-body { padding:.7rem 1rem .9rem; }
        .court-name { font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:1.18rem;
            letter-spacing:.02em; line-height:1.25; text-decoration:none; color:var(--ink); }
        .court-name:hover { color:var(--teren-dark); }
        .court-cover { position:relative; display:block; text-decoration:none; cursor:zoom-in; }
        .court-cover img { width:100%; height:172px; object-fit:cover; display:block; }
        .court-photo-count { position:absolute; right:.5rem; bottom:.5rem; background:rgba(38,43,52,.78);
            color:var(--linija); font-family:'Barlow Condensed',sans-serif; font-weight:600;
            font-size:.8rem; letter-spacing:.06em; padding:.12rem .55rem; border-radius:999px; }
        .issue-flag { display:inline-block; margin-top:.5rem; background:#f9e2de; color:#9c2c20;
            font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:.8rem;
            letter-spacing:.06em; text-transform:uppercase; padding:.1rem .55rem; border-radius:999px; }
        .filters { display:flex; flex-wrap:wrap; gap:.5rem; margin:1.2rem 0 0; align-items:center; }
        .filters input[type=search] { flex:1 1 12rem; width:auto; padding:.5rem .65rem; font-size:.92rem; }
        .chips { display:flex; gap:.4rem; flex-wrap:wrap; align-items:center; margin:.55rem 0 0; }
        .chip { font-family:'Barlow Condensed',sans-serif; font-weight:600; font-size:.88rem;
            letter-spacing:.05em; text-transform:uppercase; white-space:nowrap;
            padding:.32rem .8rem; border-radius:999px; border:1.5px solid var(--line);
            background:#fff; color:var(--ink); cursor:pointer; text-decoration:none;
            transition:border-color .15s, background .15s, color .15s; }
        .chip:hover { border-color:var(--teren); }
        .chip.active { background:var(--asfalt); border-color:var(--asfalt); color:var(--linija); }
        .chip.active[data-value="issue"] { background:var(--crveno); border-color:var(--crveno); }
        .chip-sep { width:1.5px; height:1.2rem; background:var(--line); margin:0 .2rem; flex:0 0 auto; }
        @media (max-width:640px) {
            .chips { flex-wrap:nowrap; overflow-x:auto; padding-bottom:.3rem;
                -webkit-overflow-scrolling:touch; scrollbar-width:none; }
            .chips::-webkit-scrollbar { display:none; }
        }

        .badge { display: inline-block; padding: .13rem .6rem; border-radius: 999px;
            font-family: 'Barlow Condensed', sans-serif; font-weight: 600; font-size: .82rem;
            letter-spacing: .06em; text-transform: uppercase; line-height: 1.55; }
        .badge-gray { background: #eceee7; color: #4a5160; }
        .badge-info { background: #e0eaf6; color: #29517f; }
        .badge-warning { background: #faeecd; color: #7c5410; }
        .badge-success { background: #ddeee1; color: #1f5c38; }
        .badge-danger { background: #f9e2de; color: #9c2c20; }

        .btn { display: inline-block; background: var(--teren); color: #fff; border: 0;
            padding: .6rem 1.3rem; border-radius: .55rem;
            font-family: 'Barlow Condensed', sans-serif; font-weight: 700; font-size: 1.05rem;
            letter-spacing: .07em; text-transform: uppercase;
            cursor: pointer; text-decoration: none; transition: background .15s, transform .1s; }
        .btn:hover { background: var(--teren-dark); }
        .btn:active { transform: translateY(1px); }
        .btn-ghost { background: #fff; color: var(--asfalt); border: 1px solid var(--line); }
        .btn-ghost:hover { background: var(--linija); }

        label { display: block; font-weight: 600; font-size: .88rem; margin-bottom: .3rem; }
        input, select, textarea { width: 100%; padding: .6rem .7rem; border: 1.5px solid var(--line);
            border-radius: .55rem; font: inherit; background: #fff; color: var(--ink); }
        input:focus-visible, select:focus-visible, textarea:focus-visible {
            outline: none; border-color: var(--teren); box-shadow: 0 0 0 3px rgba(46, 122, 74, .16); }

        .muted { color: var(--muted); }
        .flash { padding: .85rem 1rem; border-radius: .55rem; margin-bottom: 1rem; font-weight: 500; }
        .flash-ok { background: #ddeee1; color: #1f5c38; }
        .field-error { color: var(--crveno); font-size: .82rem; margin-top: .25rem; }

        footer.site { background: var(--asfalt); color: var(--linija);
            border-top: 4px solid var(--teren); font-size: .84rem; }
        footer.site .wrap { padding: 1.4rem 1rem; opacity: .8; }

        @media (max-width: 640px) {
            header.site .wrap { flex-direction: column; align-items: flex-start; gap: .6rem;
                padding-top: .95rem; padding-bottom: .95rem; }
            .brand-text { font-size: 1.18rem; }
            nav.top { gap: 1.5rem; }
            main { padding: 1.25rem 0 2.4rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
        {!! $styles ?? '' !!}
    </style>
    {{ $head }}
</head>
<body>
    <header class="site">
        <div class="wrap">
            <a href="{{ route('tereni.map') }}" class="brand">
                {{-- Mini court glyph — same motif as the favicon. --}}
                <svg class="brand-mark" viewBox="0 0 30 21" width="30" height="21" fill="none" aria-hidden="true">
                    <g stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <rect x="1.5" y="1.5" width="27" height="18" rx="2.5" />
                        <line x1="15" y1="1.5" x2="15" y2="19.5" />
                        <circle cx="15" cy="10.5" r="3.6" />
                    </g>
                </svg>
                <span class="brand-text">
                    Tereni Medijana
                    @if (mb_strtolower($siteName) !== 'tereni medijana')
                        <small>{{ $siteName }}</small>
                    @else
                        <small>Gradska opština Medijana</small>
                    @endif
                </span>
            </a>
            <nav class="top">
                <a href="{{ route('tereni.map') }}" @class(['active' => request()->routeIs('tereni.map')])>Mapa</a>
                <a href="{{ route('tereni.list') }}" @class(['active' => request()->routeIs('tereni.list')])>Svi tereni</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="wrap">
            {{ $slot }}
        </div>
    </main>

    <footer class="site">
        <div class="wrap">
            © {{ date('Y') }} {{ $siteName }} · Prijavi stanje terena u Medijani. Bez registracije.
        </div>
    </footer>

    {{ $scripts ?? '' }}
</body>
</html>
