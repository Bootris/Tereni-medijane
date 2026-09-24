<x-tereni.layout title="Uputstva" description="Kako radi Tereni Medijane: čemu služi, kako prijaviti problem na terenu, šta se dešava posle prijave i pravila korišćenja.">
    <x-slot:styles>
        .hero h1 { margin:.15rem 0 .6rem; font-size:clamp(1.9rem,5vw,2.6rem); }
        .hero .lead { font-size:1.08rem; max-width:44rem; margin:0; }
        .guide { display:grid; grid-template-columns:13rem 1fr; gap:1.6rem; align-items:start; margin-top:1.6rem; }
        .toc { position:sticky; top:5.6rem; padding:1rem 1.1rem; }
        .toc ol { list-style:none; margin:.5rem 0 0; padding:0; counter-reset:toc; }
        .toc li { counter-increment:toc; }
        .toc a { display:flex; gap:.5rem; padding:.28rem 0; text-decoration:none; color:var(--ink);
            font-weight:500; font-size:.93rem; }
        .toc a::before { content:counter(toc, decimal-leading-zero); font-family:'Barlow Condensed',sans-serif;
            font-weight:700; color:var(--teren); }
        .toc a:hover { color:var(--teren-dark); }
        .guide section { padding:1.3rem 1.4rem; margin-bottom:1rem; scroll-margin-top:5.6rem; }
        .guide section > h2 { margin:0 0 .7rem; }
        .guide section p:last-child, .guide section ul:last-child { margin-bottom:0; }
        .guide section ul, .guide section ol { padding-left:1.2rem; }
        .guide section li p { margin:.15rem 0; }
        .guide section > div > ol { list-style:none; padding:0; counter-reset:step; display:grid; gap:.75rem; }
        .guide section > div > ol > li { counter-increment:step; display:grid; grid-template-columns:2.3rem 1fr;
            gap:.8rem; align-items:center; }
        .guide section > div > ol > li::before { content:counter(step); display:grid; place-items:center; width:2.3rem; height:2.3rem;
            border-radius:999px; background:var(--asfalt); color:var(--signal);
            font-family:'Anton',sans-serif; font-size:1.1rem; }
        .guide section ul.ladder, .guide section ul.cats { padding:0; }
        .ladder { list-style:none; margin:.4rem 0 0; padding:0; display:grid; gap:.55rem; }
        .ladder li { display:grid; grid-template-columns:9.5rem 1fr; gap:.8rem; align-items:baseline; }
        .ladder .badge { justify-self:start; }
        .cats { list-style:none; margin:.4rem 0 0; padding:0; display:grid; gap:.5rem;
            grid-template-columns:repeat(auto-fill, minmax(9rem, 1fr)); }
        .cats li { border:1.5px solid var(--line); border-radius:.55rem; padding:.45rem .7rem; font-weight:600; }
        .guide blockquote { border-left:4px solid var(--signal); background:#fbf6e6; padding:.75rem 1rem;
            border-radius:0 .55rem .55rem 0; margin:.9rem 0 0; }
        .guide blockquote p { margin:0; }
        .guide section h3 { font-size:1rem; margin:1rem 0 .3rem; }
        .guide section h3:first-child { margin-top:0; }
        .cta { display:flex; flex-wrap:wrap; gap:.6rem; margin-top:1rem; }
        @media (max-width:760px) {
            .guide { grid-template-columns:1fr; gap:1rem; }
            .toc { position:static; }
            .guide section { padding:1.1rem 1rem; }
            .ladder li { grid-template-columns:1fr; gap:.2rem; }
        }
    </x-slot:styles>

    @php
        $guide = \App\Support\Tereni\GuideContent::get();
        $sections = \App\Support\Tereni\GuideContent::renderedSections();
    @endphp

    <div class="hero">
        <span class="eyebrow">Kako sve ovo radi</span>
        <h1 class="display rule">Uputstva</h1>
        @if (filled($guide['lead']))
            <p class="lead">{{ $guide['lead'] }}</p>
        @endif
    </div>

    <div class="guide">
        <nav class="card toc" aria-label="Sadržaj">
            <span class="eyebrow">Sadržaj</span>
            <ol>
                @foreach ($sections as $section)
                    <li><a href="#{{ $section['id'] }}">{{ $section['title'] }}</a></li>
                @endforeach
            </ol>
        </nav>

        <div>
            @foreach ($sections as $section)
                <section id="{{ $section['id'] }}" class="card">
                    <h2 class="section-title rule">{{ $section['title'] }}</h2>
                    {{-- Written by editors in the admin rich editor. --}}
                    <div>{!! $section['html'] !!}</div>

                    @if ($loop->last)
                        <div class="cta">
                            <a href="{{ route('tereni.map') }}" class="btn">Otvori mapu</a>
                            <a href="{{ route('tereni.list') }}" class="btn btn-ghost">Svi tereni</a>
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
</x-tereni.layout>
