<x-tereni.layout title="Mapa terena" description="Javna mapa sportskih terena u Medijani — prijavi stanje skeniranjem QR koda ili klikom na teren.">
    <x-slot:head>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    </x-slot:head>

    <h1 style="margin:.2rem 0 .3rem;font-size:1.6rem">Tereni u Medijani</h1>
    <p class="muted" style="margin-top:0">
        Klikni teren na mapi da vidiš detalje i prijaviš problem (koš, mreža, podloga, osvetljenje, ograda, smeće).
    </p>

    <div id="map" class="card" style="height:520px;margin-top:1rem;overflow:hidden"></div>

    @if ($courts->isEmpty())
        <p class="muted" style="margin-top:1rem">Još nema unetih terena na mapi.</p>
    @else
        {{-- No-JS / SEO fallback: the same fields as a plain list. --}}
        <h2 style="font-size:1.15rem;margin:1.5rem 0 .5rem">Svi tereni</h2>
        <ul style="list-style:none;padding:0;margin:0;display:grid;gap:.5rem;grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
            @foreach ($courts as $court)
                <li class="card" style="padding:.85rem 1rem">
                    <a href="{{ $court['url'] }}" style="font-weight:600;text-decoration:none">{{ $court['name'] }}</a>
                    <div class="muted" style="font-size:.85rem">
                        @if ($court['facility']){{ $court['facility'] }} · @endif{{ $court['type'] }} · {{ $court['access'] }}
                    </div>
                </li>
            @endforeach
        </ul>
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

            const markers = [];
            courts.forEach((c) => {
                if (c.lat == null || c.lng == null) return;
                const m = L.marker([c.lat, c.lng]).addTo(map);
                m.bindPopup(
                    '<strong>' + escapeHtml(c.name) + '</strong><br>' +
                    (c.facility ? escapeHtml(c.facility) + '<br>' : '') +
                    '<span style="color:#6b7280">' + escapeHtml(c.type) + ' · ' + escapeHtml(c.access) + '</span><br>' +
                    '<a href="' + c.url + '">Detalji i prijava →</a>'
                );
                markers.push(m);
            });

            if (markers.length) {
                map.fitBounds(L.featureGroup(markers).getBounds().pad(0.2));
            }

            function escapeHtml(s) {
                return String(s ?? '').replace(/[&<>"']/g, (ch) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                }[ch]));
            }
        </script>
    </x-slot:scripts>
</x-tereni.layout>
