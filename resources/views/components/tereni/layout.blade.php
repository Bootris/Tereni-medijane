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
    <style>
        :root {
            --brand: #15803d;
            --brand-dark: #14532d;
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #e5e7eb;
            --bg: #f8faf9;
            --card: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            color: var(--ink);
            background: var(--bg);
            line-height: 1.5;
        }
        a { color: var(--brand-dark); }
        .wrap { max-width: 960px; margin: 0 auto; padding: 0 1rem; }
        header.site {
            background: var(--brand-dark);
            color: #fff;
        }
        header.site .wrap {
            display: flex; align-items: center; justify-content: space-between;
            padding-top: .85rem; padding-bottom: .85rem;
        }
        header.site a { color: #fff; text-decoration: none; }
        .brand { font-weight: 700; font-size: 1.15rem; letter-spacing: .01em; }
        .brand small { display: block; font-weight: 400; font-size: .72rem; opacity: .8; letter-spacing: .04em; text-transform: uppercase; }
        nav.top a { margin-left: 1.25rem; font-size: .9rem; opacity: .9; }
        nav.top a:hover { opacity: 1; }
        main { padding: 1.5rem 0 3rem; }
        .card { background: var(--card); border: 1px solid var(--line); border-radius: .75rem; }
        .badge {
            display: inline-block; padding: .15rem .55rem; border-radius: 999px;
            font-size: .78rem; font-weight: 600; line-height: 1.4;
        }
        .badge-gray { background: #f3f4f6; color: #374151; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .btn {
            display: inline-block; background: var(--brand); color: #fff; border: 0;
            padding: .6rem 1.1rem; border-radius: .55rem; font-weight: 600; font-size: .95rem;
            cursor: pointer; text-decoration: none;
        }
        .btn:hover { background: var(--brand-dark); }
        .btn-ghost { background: #fff; color: var(--brand-dark); border: 1px solid var(--line); }
        label { display: block; font-weight: 600; font-size: .88rem; margin-bottom: .3rem; }
        input, select, textarea {
            width: 100%; padding: .55rem .65rem; border: 1px solid var(--line);
            border-radius: .5rem; font: inherit; background: #fff;
        }
        .muted { color: var(--muted); }
        .flash { padding: .85rem 1rem; border-radius: .55rem; margin-bottom: 1rem; font-weight: 500; }
        .flash-ok { background: #dcfce7; color: #166534; }
        .field-error { color: #991b1b; font-size: .82rem; margin-top: .25rem; }
        footer.site { border-top: 1px solid var(--line); color: var(--muted); font-size: .82rem; }
        footer.site .wrap { padding: 1.5rem 1rem; }
        {!! $styles ?? '' !!}
    </style>
    {{ $head }}
</head>
<body>
    <header class="site">
        <div class="wrap">
            <a href="{{ route('tereni.map') }}" class="brand">
                Tereni Medijana
                <small>{{ $siteName }}</small>
            </a>
            <nav class="top">
                <a href="{{ route('tereni.map') }}">Mapa terena</a>
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
