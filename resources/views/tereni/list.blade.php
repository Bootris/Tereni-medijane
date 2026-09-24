<x-tereni.layout title="Svi tereni" description="Svi sportski tereni u Medijani - pretraga po sportu, stanju i dostupnosti.">
    <x-slot:styles>
        .hero h1 { margin:.15rem 0 .45rem; font-size:clamp(1.9rem,5vw,2.6rem); }
        .hero .total { font-family:'Barlow',sans-serif; font-weight:500; font-size:1rem;
            letter-spacing:0; color:var(--muted); }
        .filters .btn { font-size:.95rem; padding:.5rem 1rem; }
        .pager { display:flex; gap:.4rem; justify-content:center; flex-wrap:wrap; margin:1.6rem 0 0; }
        .pager .chip { min-width:2.2rem; text-align:center; }
        .pager .off { opacity:.4; pointer-events:none; }
        .empty { text-align:center; padding:2.5rem 1rem; }
    </x-slot:styles>

    @php
        $chipUrl = fn (array $overrides) => route('tereni.list', array_filter(
            array_merge($filters, $overrides),
            fn ($v) => $v !== null && $v !== '',
        ));
    @endphp

    <div class="hero">
        <span class="eyebrow">Javna mapa terena</span>
        <h1 class="display rule">Svi tereni <span class="total">({{ $courts->total() }})</span></h1>
    </div>

    {{-- Server-side filters: chips are plain GET links, so pagination,
         back-button and no-JS all behave. --}}
    <form method="GET" action="{{ route('tereni.list') }}" class="filters">
        <input name="q" type="search" value="{{ $filters['q'] }}"
            placeholder="Pretraga: teren, škola, naselje…" aria-label="Pretraga terena">
        @foreach (['sport', 'stanje', 'javno'] as $keep)
            @if ($filters[$keep])
                <input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">
            @endif
        @endforeach
        <button type="submit" class="btn btn-ghost">Traži</button>
    </form>

    <div class="chips" role="group" aria-label="Tip sporta">
        <a href="{{ $chipUrl(['sport' => null, 'page' => null]) }}" class="chip @if (!$filters['sport']) active @endif">Svi sportovi</a>
        @foreach ($types as $t)
            <a href="{{ $chipUrl(['sport' => $t['value'], 'page' => null]) }}"
                class="chip @if ($filters['sport'] === $t['value']) active @endif">{{ $t['icon'] }} {{ $t['label'] }}</a>
        @endforeach
    </div>
    <div class="chips" role="group" aria-label="Stanje i dostupnost terena">
        <a href="{{ $chipUrl(['stanje' => null, 'page' => null]) }}" class="chip @if (!$filters['stanje']) active @endif">Svi tereni</a>
        <a href="{{ $chipUrl(['stanje' => 'ok', 'page' => null]) }}" class="chip @if ($filters['stanje'] === 'ok') active @endif">Bez prijava</a>
        <a href="{{ $chipUrl(['stanje' => 'issue', 'page' => null]) }}" class="chip @if ($filters['stanje'] === 'issue') active @endif" data-value="issue">⚠ Sa problemom</a>
        <span class="chip-sep" aria-hidden="true"></span>
        <a href="{{ $chipUrl(['javno' => $filters['javno'] ? null : 1, 'page' => null]) }}"
            class="chip @if ($filters['javno']) active @endif">Samo javno dostupni</a>
    </div>

    @if ($courts->isEmpty())
        <div class="card empty" style="margin-top:1.2rem">
            <p style="margin:0 0 .6rem"><strong>Nijedan teren ne odgovara izabranim filterima.</strong></p>
            <a href="{{ route('tereni.list') }}" class="btn btn-ghost">Poništi filtere</a>
        </div>
    @else
        <ul class="court-grid" style="margin-top:1.2rem">
            @foreach ($courts as $court)
                <x-tereni.court-card :court="$court" />
            @endforeach
        </ul>

        @if ($courts->hasPages())
            <nav class="pager" aria-label="Stranice">
                @if ($courts->onFirstPage())
                    <span class="chip off" aria-hidden="true">‹</span>
                @else
                    <a href="{{ $courts->previousPageUrl() }}" class="chip" rel="prev" aria-label="Prethodna strana">‹</a>
                @endif
                @foreach ($courts->getUrlRange(1, $courts->lastPage()) as $page => $pageUrl)
                    @if ($page === $courts->currentPage())
                        <span class="chip active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $pageUrl }}" class="chip">{{ $page }}</a>
                    @endif
                @endforeach
                @if ($courts->hasMorePages())
                    <a href="{{ $courts->nextPageUrl() }}" class="chip" rel="next" aria-label="Sledeća strana">›</a>
                @else
                    <span class="chip off" aria-hidden="true">›</span>
                @endif
            </nav>
        @endif
    @endif

    <x-slot:scripts>
        <x-tereni.gallery-lightbox />
    </x-slot:scripts>
</x-tereni.layout>
