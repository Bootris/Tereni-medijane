<x-tereni.layout title="Mapa terena" description="Javna mapa sportskih terena u Medijani — prijavi stanje skeniranjem QR koda ili klikom na teren.">
    <x-slot:head>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    </x-slot:head>

    <x-slot:styles>
        .hero h1 { margin:.15rem 0 .45rem; font-size:clamp(1.9rem,5vw,2.6rem); }
        .hero p { margin:.55rem 0 0; max-width:54ch; }
        .hero-meta { display:flex; gap:.45rem; flex-wrap:wrap; margin-top:.75rem; }
        /* Map in a shell: isolation keeps Leaflet's z-indexes (200–1000) from
           painting over page overlays; the locate button floats on the map. */
        .map-shell { position:relative; isolation:isolate; overflow:hidden; margin-top:.7rem; }
        #map { height:clamp(320px, 52vh, 460px); }
        .btn-locate { position:absolute; right:.65rem; top:.65rem; z-index:1001;
            display:inline-flex; align-items:center; gap:.35rem;
            font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:.85rem;
            letter-spacing:.07em; text-transform:uppercase;
            background:#fff; color:var(--asfalt); border:1.5px solid var(--line);
            border-radius:999px; padding:.42rem .85rem; cursor:pointer;
            box-shadow:0 2px 8px rgba(38,43,52,.22); transition:border-color .15s; }
        .btn-locate:hover { border-color:var(--teren); }
        @media (max-width:640px) {
            #map { height:min(58vh, 440px); min-height:330px; }
        }
        .court-marker { display:flex; align-items:center; justify-content:center;
            width:34px; height:34px; border-radius:999px; background:#fff;
            border:2px solid var(--asfalt); font-size:17px;
            box-shadow:0 2px 6px rgba(0,0,0,.25); }
        .court-marker.has-issue { border-color:var(--crveno); box-shadow:0 0 0 3px rgba(205,64,52,.25); }
        .court-marker.has-issue::after { content:'!'; position:absolute; top:-6px; right:-4px;
            width:15px; height:15px; border-radius:999px; background:var(--crveno); color:#fff;
            font:700 11px/15px 'Barlow',sans-serif; text-align:center; }
        .leaflet-container, .leaflet-popup-content { font-family:'Barlow',sans-serif; }
        .leaflet-popup-content-wrapper { border-radius:.7rem; }
        .leaflet-popup-content img.popup-photo { width:100%; height:120px; object-fit:cover;
            border-radius:.4rem; display:block; margin:.4rem 0 .3rem; }
        .issue-flag { display:inline-block; margin-top:.5rem; background:#f9e2de; color:#9c2c20;
            font-family:'Barlow Condensed',sans-serif; font-weight:700; font-size:.8rem;
            letter-spacing:.06em; text-transform:uppercase; padding:.1rem .55rem; border-radius:999px; }
        .semafor { margin-top:1.8rem; background:var(--asfalt); border-radius:.9rem;
            color:var(--linija); padding:1.05rem 1.25rem 1.2rem; }
        .semafor .eyebrow { color:var(--linija); opacity:.6; }
        .s-grid { display:flex; flex-wrap:wrap; margin-top:.5rem; }
        .s-item { flex:1 1 9rem; padding:.35rem 1rem .4rem; text-align:center;
            border-left:2px dashed rgba(245,242,232,.22); }
        .s-item:first-child { border-left:0; padding-left:0; }
        .s-item strong { display:block; font-family:'Anton',sans-serif; font-weight:400;
            font-size:2.35rem; line-height:1.15; letter-spacing:.02em; }
        .s-item span { font-family:'Barlow Condensed',sans-serif; font-weight:600; font-size:.78rem;
            letter-spacing:.12em; text-transform:uppercase; opacity:.72; }
        .s-item.s-open strong { color:var(--signal); }
        @media (max-width:640px) {
            .s-grid { display:grid; grid-template-columns:1fr 1fr; gap:.7rem .5rem; }
            .s-item { border-left:0; padding:0; }
        }
    </x-slot:styles>

    <div class="hero">
        <span class="eyebrow">Javna mapa terena</span>
        <h1 class="display rule">Tereni u Medijani</h1>
        <p class="muted">
            Klikni teren na mapi da vidiš detalje i prijaviš problem (koš, mreža, podloga, osvetljenje, ograda, smeće).
        </p>
        <div class="hero-meta">
            <span class="badge badge-gray">{{ $courts->count() }} terena</span>
            @if (($stats['open'] ?? 0) > 0)
                <span class="badge badge-danger">⚠ {{ $stats['open'] }} otvorenih prijava</span>
            @elseif ($courts->isNotEmpty())
                <span class="badge badge-success">Nema otvorenih prijava</span>
            @endif
        </div>
    </div>

    {{-- Filters + search — client-side over the same dataset the map uses.
         Tap chips instead of native selects: no dropdown, phone-friendly. --}}
    <div class="filters">
        <input id="f-search" type="search" placeholder="Pretraga: teren, škola, naselje…" aria-label="Pretraga terena">
    </div>
    <div class="chips" role="group" aria-label="Tip sporta">
        <button type="button" class="chip active" data-group="type" data-value="" aria-pressed="true">Svi sportovi</button>
        @foreach ($types as $t)
            <button type="button" class="chip" data-group="type" data-value="{{ $t['value'] }}" aria-pressed="false">{{ $t['icon'] }} {{ $t['label'] }}</button>
        @endforeach
    </div>
    <div class="chips" role="group" aria-label="Stanje i dostupnost terena">
        <button type="button" class="chip active" data-group="state" data-value="" aria-pressed="true">Svi tereni</button>
        <button type="button" class="chip" data-group="state" data-value="ok" aria-pressed="false">Bez prijava</button>
        <button type="button" class="chip" data-group="state" data-value="issue" aria-pressed="false">⚠ Sa problemom</button>
        <span class="chip-sep" aria-hidden="true"></span>
        <button type="button" class="chip" data-group="pub" aria-pressed="false">Samo javno dostupni</button>
    </div>

    <div class="map-shell card">
        <div id="map" aria-label="Mapa terena"></div>
        <button id="btn-locate" type="button" class="btn-locate">📍 Moja lokacija</button>
    </div>

    @if ($courts->isEmpty())
        <p class="muted" style="margin-top:1rem">Još nema unetih terena na mapi.</p>
    @else
        {{-- First few fields as cards; the full directory lives at /tereni.
             Cards are wired to the map — click centers and opens the popup. --}}
        <h2 class="section-title rule" style="margin:1.8rem 0 .8rem">Tereni <span id="count" class="muted" style="font-family:'Barlow',sans-serif;font-weight:500;font-size:.85rem;letter-spacing:0;text-transform:none"></span></h2>
        <ul id="court-list" class="court-grid">
            @foreach ($courts->take(6) as $i => $court)
                <x-tereni.court-card :court="$court" :idx="$i" />
            @endforeach
        </ul>
        <p style="text-align:center;margin:1.3rem 0 0">
            <a href="{{ route('tereni.list') }}" class="btn btn-ghost">Pogledaj sve terene ({{ $courts->count() }}) →</a>
        </p>
    @endif

    {{-- The scoreboard — visible effect motivates reporting. --}}
    @if (($stats['resolved_total'] ?? 0) > 0 || ($stats['open'] ?? 0) > 0)
        <section class="semafor" aria-label="Stanje prijava">
            <span class="eyebrow">Semafor</span>
            <div class="s-grid">
                <div class="s-item"><strong>{{ $stats['resolved_total'] }}</strong><span>rešenih problema ukupno</span></div>
                <div class="s-item"><strong>{{ $stats['resolved_month'] }}</strong><span>rešeno ovog meseca</span></div>
                @if ($stats['avg_days'] !== null)
                    <div class="s-item"><strong>{{ str_replace('.', ',', (string) $stats['avg_days']) }}</strong><span>dana prosečno do rešenja</span></div>
                @endif
                <div class="s-item s-open"><strong>{{ $stats['open'] }}</strong><span>trenutno otvorenih prijava</span></div>
            </div>
        </section>
    @endif

    <x-slot:scripts>
        <x-tereni.gallery-lightbox />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            const courts = @json($courts);
            const center = @json($center);

            const map = L.map('map').setView([center.lat, center.lng], center.zoom);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap',
            }).addTo(map);

            // One marker per court, icon by sport, red ring when it has an
            // open reported problem — the first thing a player wants to know.
            const markers = [];
            courts.forEach((c, i) => {
                if (c.lat == null || c.lng == null) { markers.push(null); return; }
                const m = L.marker([c.lat, c.lng], {
                    icon: L.divIcon({
                        className: '',
                        html: '<div class="court-marker' + (c.has_issue ? ' has-issue' : '') + '" style="position:relative">' + c.icon + '</div>',
                        iconSize: [34, 34],
                        iconAnchor: [17, 17],
                        popupAnchor: [0, -17],
                    }),
                });
                m.bindPopup(
                    '<strong>' + escapeHtml(c.icon + ' ' + c.name) + '</strong><br>' +
                    (c.facility ? escapeHtml(c.facility) + '<br>' : '') +
                    (c.photos.length ? '<img class="popup-photo popup-photo-open" data-idx="' + i + '" src="' + escapeHtml(c.photos[0]) + '" alt="">' : '') +
                    '<span style="color:#6b7280">' + escapeHtml(c.type_label) + ' · ' + escapeHtml(c.access_label) + '</span><br>' +
                    (c.has_issue ? '<span style="color:#b91c1c;font-weight:600">⚠ prijavljen problem</span><br>' : '') +
                    '<a href="' + c.url + '">Detalji i prijava →</a>'
                , { minWidth: 220 });
                markers.push(m);
            });

            // Filters drive both the markers and the card list below.
            const els = {
                search: document.getElementById('f-search'),
                count: document.getElementById('count'),
            };
            const filter = { type: '', state: '', pub: false };

            function matches(c) {
                if (filter.type && c.type !== filter.type) return false;
                if (filter.state === 'ok' && c.has_issue) return false;
                if (filter.state === 'issue' && !c.has_issue) return false;
                if (filter.pub && c.access !== 'javno') return false;
                const q = els.search.value.trim().toLowerCase();
                if (q && !((c.name + ' ' + (c.facility || '')).toLowerCase().includes(q))) return false;
                return true;
            }

            function apply() {
                const visible = [];
                courts.forEach((c, i) => {
                    const on = matches(c);
                    const m = markers[i];
                    if (m) { on ? m.addTo(map) : map.removeLayer(m); }
                    const card = document.querySelector('.court-card[data-idx="' + i + '"]');
                    if (card) card.style.display = on ? '' : 'none';
                    if (on && m) visible.push(m);
                });
                if (els.count) {
                    const total = courts.length;
                    const shown = courts.filter(matches).length;
                    els.count.textContent = shown === total ? '(' + total + ')' : '(' + shown + ' od ' + total + ')';
                }
                if (visible.length) {
                    map.fitBounds(L.featureGroup(visible).getBounds().pad(0.2));
                }
            }

            document.querySelectorAll('.chip[data-group]').forEach((chip) => {
                chip.addEventListener('click', () => {
                    const group = chip.dataset.group;
                    if (group === 'pub') {
                        filter.pub = !filter.pub;
                        chip.classList.toggle('active', filter.pub);
                        chip.setAttribute('aria-pressed', String(filter.pub));
                    } else {
                        filter[group] = chip.dataset.value;
                        document.querySelectorAll('.chip[data-group="' + group + '"]').forEach((c) => {
                            const on = c === chip;
                            c.classList.toggle('active', on);
                            c.setAttribute('aria-pressed', String(on));
                        });
                    }
                    apply();
                });
            });
            ['input', 'change'].forEach((evt) => els.search.addEventListener(evt, apply));
            apply();

            // Popup photo → fullscreen gallery (card covers bind themselves).
            map.on('popupopen', (e) => {
                const photo = e.popup.getElement().querySelector('.popup-photo-open');
                if (!photo) return;
                photo.style.cursor = 'zoom-in';
                photo.addEventListener('click', () => {
                    const c = courts[Number(photo.dataset.idx)];
                    window.tereniGallery.open(c.photos, 0, c.icon + ' ' + c.name);
                });
            });

            // Card click → center the map on that court and open its popup
            // (links inside the card still navigate normally).
            document.querySelectorAll('.court-card').forEach((card) => {
                card.addEventListener('click', (e) => {
                    if (e.target.closest('a')) return;
                    const m = markers[Number(card.dataset.idx)];
                    if (!m) return;
                    map.flyTo(m.getLatLng(), Math.max(map.getZoom(), 17));
                    m.openPopup();
                    document.getElementById('map').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                });
            });

            // "My location" → blue dot + fly to it, so the nearest court is obvious.
            let youMarker = null;
            document.getElementById('btn-locate').addEventListener('click', () => {
                if (!navigator.geolocation) { alert('Tvoj pregledač ne podržava lociranje.'); return; }
                navigator.geolocation.getCurrentPosition((pos) => {
                    const at = [pos.coords.latitude, pos.coords.longitude];
                    if (youMarker) map.removeLayer(youMarker);
                    youMarker = L.circleMarker(at, {
                        radius: 8, color: '#1d4ed8', fillColor: '#3b82f6', fillOpacity: .9, weight: 2,
                    }).addTo(map).bindPopup('Ti si ovde');
                    map.flyTo(at, 15);
                }, () => alert('Ne mogu da odredim lokaciju — proveri dozvolu za lociranje.'));
            });

            function escapeHtml(s) {
                return String(s ?? '').replace(/[&<>"']/g, (ch) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                }[ch]));
            }
        </script>
    </x-slot:scripts>
</x-tereni.layout>
