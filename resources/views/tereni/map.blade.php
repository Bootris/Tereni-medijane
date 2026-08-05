<x-tereni.layout title="Mapa terena" description="Javna mapa sportskih terena u Medijani — prijavi stanje skeniranjem QR koda ili klikom na teren.">
    <x-slot:head>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    </x-slot:head>

    <x-slot:styles>
        .court-marker { display:flex; align-items:center; justify-content:center;
            width:34px; height:34px; border-radius:999px; background:#fff;
            border:2px solid var(--brand-dark); font-size:17px;
            box-shadow:0 2px 6px rgba(0,0,0,.25); }
        .court-marker.has-issue { border-color:#dc2626; box-shadow:0 0 0 3px rgba(220,38,38,.25); }
        .court-marker.has-issue::after { content:'!'; position:absolute; top:-6px; right:-4px;
            width:15px; height:15px; border-radius:999px; background:#dc2626; color:#fff;
            font:700 11px/15px Inter,sans-serif; text-align:center; }
        .filters { display:flex; flex-wrap:wrap; gap:.5rem; margin:.9rem 0 .6rem; align-items:center; }
        .filters input[type=search], .filters select { width:auto; min-width:9.5rem; padding:.45rem .6rem; font-size:.9rem; }
        .filters input[type=search] { flex:1 1 12rem; }
        .stats { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:1rem; }
        .stats .stat { flex:1 1 10rem; padding:.7rem .9rem; text-align:center; }
        .stats .stat strong { display:block; font-size:1.35rem; color:var(--brand-dark); }
        .stats .stat span { font-size:.78rem; }
        .court-card { padding:.85rem 1rem; cursor:pointer; }
        .court-card:hover { border-color:var(--brand); }
        .issue-flag { color:#b91c1c; font-size:.78rem; font-weight:600; }
    </x-slot:styles>

    <h1 style="margin:.2rem 0 .3rem;font-size:1.6rem">Tereni u Medijani</h1>
    <p class="muted" style="margin-top:0">
        Klikni teren na mapi da vidiš detalje i prijaviš problem (koš, mreža, podloga, osvetljenje, ograda, smeće).
    </p>

    {{-- Filters + search — client-side over the same dataset the map uses. --}}
    <div class="filters">
        <input id="f-search" type="search" placeholder="Pretraga: teren, škola, naselje…" aria-label="Pretraga terena">
        <select id="f-type" aria-label="Tip sporta">
            <option value="">Svi sportovi</option>
            @foreach ($types as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <select id="f-state" aria-label="Stanje terena">
            <option value="">Svi tereni</option>
            <option value="ok">Bez prijavljenih problema</option>
            <option value="issue">Sa prijavljenim problemom</option>
        </select>
        <label style="display:flex;align-items:center;gap:.35rem;font-weight:400;font-size:.9rem;margin:0">
            <input id="f-public" type="checkbox" style="width:auto"> samo javno dostupni
        </label>
        <button id="btn-locate" type="button" class="btn btn-ghost" style="font-size:.85rem;padding:.45rem .8rem">📍 Moja lokacija</button>
    </div>

    <div id="map" class="card" style="height:520px;margin-top:.4rem;overflow:hidden"></div>

    @if ($courts->isEmpty())
        <p class="muted" style="margin-top:1rem">Još nema unetih terena na mapi.</p>
    @else
        {{-- No-JS / SEO fallback: the same fields as a plain list. Cards are
             wired to the map — click centers and opens the marker popup. --}}
        <h2 style="font-size:1.15rem;margin:1.5rem 0 .5rem">Svi tereni <span id="count" class="muted" style="font-weight:400;font-size:.85rem"></span></h2>
        <ul id="court-list" style="list-style:none;padding:0;margin:0;display:grid;gap:.5rem;grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
            @foreach ($courts as $i => $court)
                <li class="card court-card" data-idx="{{ $i }}"
                    data-type="{{ $court['type'] }}" data-access="{{ $court['access'] }}"
                    data-issue="{{ $court['has_issue'] ? 1 : 0 }}"
                    data-text="{{ mb_strtolower($court['name'].' '.($court['facility'] ?? '')) }}">
                    <a href="{{ $court['url'] }}" style="font-weight:600;text-decoration:none">{{ $court['icon'] }} {{ $court['name'] }}</a>
                    <div class="muted" style="font-size:.85rem">
                        @if ($court['facility']){{ $court['facility'] }} · @endif{{ $court['type_label'] }} · {{ $court['access_label'] }}
                    </div>
                    @if ($court['has_issue'])
                        <div class="issue-flag">⚠ prijavljen problem</div>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    {{-- Civic-tech stats bar: visible effect motivates reporting. --}}
    @if (($stats['resolved_total'] ?? 0) > 0 || ($stats['open'] ?? 0) > 0)
        <div class="stats">
            <div class="card stat"><strong>{{ $stats['resolved_total'] }}</strong><span class="muted">rešenih problema ukupno</span></div>
            <div class="card stat"><strong>{{ $stats['resolved_month'] }}</strong><span class="muted">rešeno ovog meseca</span></div>
            @if ($stats['avg_days'] !== null)
                <div class="card stat"><strong>{{ str_replace('.', ',', (string) $stats['avg_days']) }}</strong><span class="muted">dana prosečno do rešenja</span></div>
            @endif
            <div class="card stat"><strong>{{ $stats['open'] }}</strong><span class="muted">trenutno otvorenih prijava</span></div>
        </div>
    @endif

    <x-slot:scripts>
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
                    '<span style="color:#6b7280">' + escapeHtml(c.type_label) + ' · ' + escapeHtml(c.access_label) + '</span><br>' +
                    (c.has_issue ? '<span style="color:#b91c1c;font-weight:600">⚠ prijavljen problem</span><br>' : '') +
                    '<a href="' + c.url + '">Detalji i prijava →</a>'
                );
                markers.push(m);
            });

            // Filters drive both the markers and the card list below.
            const els = {
                search: document.getElementById('f-search'),
                type: document.getElementById('f-type'),
                state: document.getElementById('f-state'),
                pub: document.getElementById('f-public'),
                count: document.getElementById('count'),
            };

            function matches(c) {
                if (els.type.value && c.type !== els.type.value) return false;
                if (els.state.value === 'ok' && c.has_issue) return false;
                if (els.state.value === 'issue' && !c.has_issue) return false;
                if (els.pub.checked && c.access !== 'javno') return false;
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

            ['input', 'change'].forEach((evt) => {
                els.search.addEventListener(evt, apply);
                els.type.addEventListener(evt, apply);
                els.state.addEventListener(evt, apply);
                els.pub.addEventListener(evt, apply);
            });
            apply();

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
