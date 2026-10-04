@php
    $isPrivacy = $doc === 'privacy';
    $title = $isPrivacy ? 'Politika privatnosti' : 'Uslovi korišćenja';
    $operator = [
        'name' => $site['legal_name'] ?? null,
        'id' => $site['legal_id'] ?? null,
        'address' => $site['address'] ?? null,
        'email' => $site['email'] ?? null,
        'phone' => $site['phone'] ?? null,
    ];
    $updated = '04.10.2026.';
@endphp
<x-tereni.layout :title="$title" :description="$isPrivacy ? 'Koje podatke prikuplja Tereni Medijane, zašto, koliko dugo ih čuva i koja prava imate.' : 'Pravila korišćenja servisa Tereni Medijane: prijave, fotografije, moderacija i odgovornost.'">
    <x-slot:styles>
        .hero h1 { margin:.15rem 0 .6rem; font-size:clamp(1.9rem,5vw,2.6rem); }
        .hero .lead { font-size:1.02rem; margin:0; }
        .legal { display:grid; grid-template-columns:13rem 1fr; gap:1.6rem; align-items:start; margin-top:1.6rem; }
        .toc { position:sticky; top:5.6rem; padding:1rem 1.1rem; }
        .toc ul { list-style:none; margin:.5rem 0 0; padding:0; }
        .toc a { display:block; padding:.28rem 0; text-decoration:none; color:var(--ink); font-weight:500; }
        .toc a:hover, .toc a.current { color:var(--teren-dark); }
        .toc a.current { font-weight:700; }
        .legal .doc { padding:1.3rem 1.4rem; }
        .legal .doc h2 { margin:1.4rem 0 .6rem; }
        .legal .doc h2:first-of-type { margin-top:.2rem; }
        .legal .doc h3 { font-size:1rem; margin:1rem 0 .3rem; }
        .legal .doc ul { padding-left:1.2rem; }
        .legal .doc li { margin:.2rem 0; }
        .legal .doc blockquote { border-left:4px solid var(--signal); background:#fbf6e6; padding:.75rem 1rem;
            border-radius:0 .55rem .55rem 0; margin:.9rem 0 0; }
        .legal .doc blockquote p { margin:0; }
        @media (max-width:760px) {
            .legal { grid-template-columns:1fr; gap:1rem; }
            .toc { position:static; }
            .legal .doc { padding:1.1rem 1rem; }
        }
    </x-slot:styles>

    <div class="hero">
        <span class="eyebrow">Pravne informacije</span>
        <h1 class="display rule">{{ $title }}</h1>
        <p class="lead muted">Poslednja izmena: {{ $updated }}</p>
    </div>

    <div class="legal">
        <nav class="card toc" aria-label="Pravne informacije">
            <span class="eyebrow">Dokumenta</span>
            <ul>
                <li><a href="{{ route('tereni.privacy') }}" @class(['current' => $isPrivacy])>Politika privatnosti</a></li>
                <li><a href="{{ route('tereni.terms') }}" @class(['current' => ! $isPrivacy])>Uslovi korišćenja</a></li>
            </ul>
        </nav>

        <article class="card doc">
            @include('tereni.legal.' . $doc, ['operator' => $operator])
            <p style="margin-top:1.6rem">
                <a class="btn btn-ghost" href="{{ route('tereni.map') }}">Nazad na mapu</a>
            </p>
        </article>
    </div>
</x-tereni.layout>
