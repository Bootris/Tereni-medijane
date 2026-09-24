<x-tereni.layout :title="$court->name" :description="'Prijavi stanje terena: ' . $court->name . ($court->facility ? ' - ' . $court->facility->name : '')">
    <x-slot:styles>
        .gallery-main { position:relative; overflow:hidden; }
        .gallery-main img { width:100%; height:min(52vh,420px); object-fit:cover; display:block; cursor:zoom-in; }
        .g-nav { position:absolute; top:50%; transform:translateY(-50%); width:2.6rem; height:2.6rem;
            border:0; border-radius:999px; background:rgba(255,255,255,.92); font-size:1.5rem; line-height:1;
            cursor:pointer; box-shadow:0 1px 4px rgba(0,0,0,.25); }
        .g-nav:hover { background:#fff; }
        .g-count { position:absolute; right:.6rem; bottom:.6rem; background:rgba(15,23,42,.75);
            color:#fff; font-size:.78rem; font-weight:600; padding:.15rem .6rem; border-radius:999px; }
        .g-thumbs { display:flex; gap:.45rem; margin-top:.5rem; overflow-x:auto; padding-bottom:.2rem; }
        .g-thumb { flex:0 0 auto; border:2px solid transparent; border-radius:.5rem; padding:0;
            background:none; cursor:pointer; overflow:hidden; }
        .g-thumb.active { border-color:var(--brand); }
        .g-thumb img { width:84px; height:60px; object-fit:cover; display:block; border-radius:.35rem; }
        h1.display { margin:.2rem 0 .45rem; font-size:clamp(1.8rem,5vw,2.5rem); }
        .back-link { font-family:'Barlow Condensed',sans-serif; font-weight:600; font-size:.92rem;
            letter-spacing:.08em; text-transform:uppercase; text-decoration:none; color:var(--muted); }
        .back-link:hover { color:var(--teren-dark); }
        .report-card { border-top:4px solid var(--signal); }
        .timeline { list-style:none; padding:0; margin:.6rem 0 0; border-left:2px solid var(--line); }
        .timeline li { padding:.15rem 0 .5rem .9rem; position:relative; }
        .timeline li::before { content:''; position:absolute; left:-6px; top:.42em;
            width:8px; height:8px; border-radius:999px; background:var(--card); border:2px solid var(--teren); }
        .form-grid { display:grid; gap:1rem; grid-template-columns:1fr 1fr; }
        @media (max-width:560px) {
            .report-card .btn { width:100%; text-align:center; }
            .form-grid { grid-template-columns:1fr; }
            .form-grid > div { grid-column:auto !important; }
        }
    </x-slot:styles>

    <p style="margin:0 0 .5rem"><a href="{{ route('tereni.map') }}" class="back-link">← Mapa terena</a></p>

    <span class="eyebrow">{{ $court->type->label() }}@if ($court->facility) · {{ $court->facility->name }}@endif</span>
    <h1 class="display rule">{{ $court->name }}</h1>
    @if ($court->facility?->address)
        <p class="muted" style="margin:0 0 .7rem">{{ $court->facility->address }}</p>
    @endif

    <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin:.8rem 0 1rem">
        <span class="badge badge-{{ $court->access->color() }}">{{ $court->access->label() }}</span>
        @if ($court->surface)<span class="badge badge-gray">Podloga: {{ $court->surface }}</span>@endif
        @if ($court->dimensions)<span class="badge badge-gray">{{ $court->dimensions }}</span>@endif
        <span class="badge badge-gray">{{ $court->has_lighting ? 'Osvetljenje: da' : 'Osvetljenje: ne' }}</span>
    </div>

    @php
        $galleryUrls = array_values(array_map(
            fn (string $image) => \Illuminate\Support\Facades\Storage::disk('public')->url($image),
            $court->gallery ?? [],
        ));
    @endphp

    @if ($galleryUrls)
        {{-- Big carousel: current state of the court, browsable left/right.
             Main photo opens the fullscreen viewer. --}}
        <div style="margin:0 0 1rem">
            <div class="gallery-main card">
                <a id="g-open" href="{{ $galleryUrls[0] }}" target="_blank" rel="noopener"
                    aria-label="Uvećaj fotografiju terena">
                    <img id="g-main" src="{{ $galleryUrls[0] }}" alt="Fotografija terena - {{ $court->name }}">
                </a>
                @if (count($galleryUrls) > 1)
                    <button type="button" class="g-nav" id="g-prev" style="left:.6rem" aria-label="Prethodna fotografija">‹</button>
                    <button type="button" class="g-nav" id="g-next" style="right:.6rem" aria-label="Sledeća fotografija">›</button>
                    <span class="g-count" id="g-count">1 / {{ count($galleryUrls) }}</span>
                @endif
            </div>
            @if (count($galleryUrls) > 1)
                <div class="g-thumbs">
                    @foreach ($galleryUrls as $j => $url)
                        <button type="button" class="g-thumb @if ($j === 0) active @endif" data-g="{{ $j }}"
                            aria-label="Fotografija {{ $j + 1 }}">
                            <img src="{{ $url }}" alt="" loading="lazy">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if ($court->description)
        <p>{{ $court->description }}</p>
    @endif

    {{-- Report form --}}
    <section class="card report-card" style="padding:1.25rem;margin:1.5rem 0">
        <h2 class="section-title" style="margin:0 0 .25rem">Prijavi problem</h2>
        <p class="muted" style="margin:0 0 1rem">Bez registracije. Fotografija je obavezna. Prijava se objavljuje nakon moderacije.</p>

        @if (session('report_success'))
            <div class="flash flash-ok">Hvala! Prijava je primljena i biće objavljena nakon provere.</div>
        @endif

        @if ($errors->any())
            <div class="flash" style="background:#fee2e2;color:#991b1b">
                Molimo ispravite greške u formi.
            </div>
        @endif

        <form method="POST" action="{{ route('tereni.report.store', $court) }}" enctype="multipart/form-data">
            @csrf
            {{-- Honeypot --}}
            <div style="position:absolute;left:-9999px" aria-hidden="true">
                <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="form-grid">
                <div style="grid-column:1/-1">
                    <label for="category">Tip problema *</label>
                    <select id="category" name="category" required>
                        <option value="">- izaberi -</option>
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div style="grid-column:1/-1">
                    <label for="description">Kratak opis</label>
                    <textarea id="description" name="description" rows="3" maxlength="2000">{{ old('description') }}</textarea>
                    @error('description')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div style="grid-column:1/-1">
                    <label for="photo">Fotografija *</label>
                    <input id="photo" name="photo" type="file" accept="image/*" capture="environment" required>
                    @error('photo')<div class="field-error">{{ $message }}</div>@enderror
                </div>

                <div>
                    <label for="reporter_name">Ime (opciono)</label>
                    <input id="reporter_name" name="reporter_name" type="text" value="{{ old('reporter_name') }}" maxlength="255">
                </div>
                <div>
                    <label for="reporter_contact">Kontakt za obaveštenje (opciono)</label>
                    <input id="reporter_contact" name="reporter_contact" type="text" value="{{ old('reporter_contact') }}"
                        maxlength="255" placeholder="email ili telefon">
                </div>
            </div>

            <div style="margin-top:1rem">
                <button type="submit" class="btn">Pošalji prijavu</button>
            </div>
        </form>
    </section>

    {{-- Public timeline of reports --}}
    <section>
        <h2 class="section-title rule" style="margin:0 0 .9rem">Prijave za ovaj teren ({{ $reports->count() }})</h2>

        @forelse ($reports as $report)
            <article class="card" style="padding:1rem;margin-bottom:1rem">
                <div style="display:flex;gap:1rem;flex-wrap:wrap">
                    @if ($report->photoUrl())
                        {{-- Opens the in-page lightbox; plain link (new tab) without JS. --}}
                        <a href="{{ $report->photoUrl() }}" target="_blank" rel="noopener" class="lb-open"
                            style="flex:0 0 auto;text-align:center;text-decoration:none"
                            data-badge="{{ $report->category->label() }} · {{ $report->status->label() }}"
                            data-date="Prijavljeno {{ $report->created_at->format('d.m.Y H:i') }}"
                            data-desc="{{ $report->description }}">
                            <img src="{{ $report->photoUrl() }}" alt="Fotografija problema" loading="lazy"
                                style="width:160px;height:120px;object-fit:cover;border-radius:.5rem;border:1px solid var(--line);display:block">
                            <span class="muted" style="font-size:.75rem">🔍 klikni za uvećanje</span>
                        </a>
                    @endif

                    <div style="flex:1 1 260px;min-width:0">
                        <div style="display:flex;justify-content:space-between;gap:1rem;flex-wrap:wrap;align-items:center">
                            <div>
                                <span class="badge badge-gray">{{ $report->category->label() }}</span>
                                <span class="badge badge-{{ $report->status->color() }}">{{ $report->status->label() }}</span>
                            </div>
                            <span class="muted" style="font-size:.82rem">{{ $report->created_at->format('d.m.Y') }}</span>
                        </div>

                        @if ($report->description)
                            <p style="margin:.6rem 0 .4rem">{{ $report->description }}</p>
                        @endif

                        {{-- Status timeline - the public pressure. --}}
                        @if ($report->statusChanges->isNotEmpty())
                            <ol class="timeline">
                                @foreach ($report->statusChanges as $change)
                                    <li>
                                        <span style="font-weight:600">{{ $change->status->label() }}</span>
                                        <span class="muted" style="font-size:.8rem"> · {{ $change->created_at?->format('d.m.Y H:i') }}</span>
                                        @if ($change->note)
                                            <div class="muted" style="font-size:.85rem">{{ $change->note }}</div>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        @endif

                        @if ($report->resolution_note)
                            <p style="margin:.6rem 0 0;padding:.6rem .8rem;background:#f3f4f6;border-radius:.5rem;font-size:.9rem">
                                <strong>Komentar zaduženog:</strong> {{ $report->resolution_note }}
                            </p>
                        @endif

                        @unless ($report->is_flagged)
                            <form method="POST" action="{{ route('tereni.report.flag', $report) }}" style="margin-top:.5rem">
                                @csrf
                                <button type="submit" class="btn btn-ghost" style="font-size:.8rem;padding:.3rem .7rem">
                                    Prijavi kao neprikladno
                                </button>
                            </form>
                        @endunless
                    </div>
                </div>
            </article>
        @empty
            <p class="muted">Još nema javnih prijava za ovaj teren. Budi prvi.</p>
        @endforelse
    </section>

    <x-slot:scripts>
        <x-tereni.gallery-lightbox />

        {{-- Phone photos are 3–8 MB; server request limits are far below that.
             Downscale to max 1920px in the browser before upload; on any failure
             (old browser, exotic format) the original file is sent unchanged. --}}
        <script>
            (function () {
                const input = document.getElementById('photo');
                if (!input || typeof DataTransfer === 'undefined' || typeof createImageBitmap === 'undefined') return;

                const MAX_DIM = 1920;
                const MAX_BYTES = 1024 * 1024;

                input.addEventListener('change', async () => {
                    const file = input.files && input.files[0];
                    if (!file || !file.type.startsWith('image/') || file.type === 'image/gif') return;

                    try {
                        const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
                        const scale = Math.min(1, MAX_DIM / Math.max(bitmap.width, bitmap.height));
                        if (scale === 1 && file.size <= MAX_BYTES) return;

                        const canvas = document.createElement('canvas');
                        canvas.width = Math.round(bitmap.width * scale);
                        canvas.height = Math.round(bitmap.height * scale);
                        canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);

                        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.82));
                        if (!blob || blob.size >= file.size) return;

                        const dt = new DataTransfer();
                        dt.items.add(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
                        input.files = dt.files;
                    } catch (e) { /* keep the original file */ }
                });
            })();
        </script>

        @if ($galleryUrls)
            <script>
                (function () {
                    const photos = @json($galleryUrls);
                    const title = @json($court->type->icon() . ' ' . $court->name);
                    const main = document.getElementById('g-main');
                    const openLink = document.getElementById('g-open');
                    const count = document.getElementById('g-count');
                    const thumbs = document.querySelectorAll('.g-thumb');
                    let idx = 0;

                    function show(i) {
                        idx = (i + photos.length) % photos.length;
                        main.src = photos[idx];
                        openLink.href = photos[idx];
                        if (count) count.textContent = (idx + 1) + ' / ' + photos.length;
                        thumbs.forEach((t) => t.classList.toggle('active', Number(t.dataset.g) === idx));
                    }

                    document.getElementById('g-prev')?.addEventListener('click', () => show(idx - 1));
                    document.getElementById('g-next')?.addEventListener('click', () => show(idx + 1));
                    thumbs.forEach((t) => t.addEventListener('click', () => show(Number(t.dataset.g))));

                    openLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        window.tereniGallery.open(photos, idx, title);
                    });

                    // Swipe on the main photo too, not just in the fullscreen viewer.
                    let touchX = null;
                    main.addEventListener('touchstart', (e) => { touchX = e.changedTouches[0].clientX; }, { passive: true });
                    main.addEventListener('touchend', (e) => {
                        if (touchX === null) return;
                        const dx = e.changedTouches[0].clientX - touchX;
                        touchX = null;
                        if (Math.abs(dx) > 40) show(dx < 0 ? idx + 1 : idx - 1);
                    }, { passive: true });
                })();
            </script>
        @endif

        {{-- Lightbox: full-size photo + the exact problem (category, status, when, description). --}}
        <div id="lb" role="dialog" aria-modal="true" aria-label="Pregled prijave"
            style="display:none;position:fixed;inset:0;z-index:1200;background:rgba(15,23,42,.88);padding:1rem;align-items:center;justify-content:center">
            <figure style="margin:0;max-width:min(940px,100%);max-height:100%;display:flex;flex-direction:column;background:#fff;border-radius:.75rem;overflow:hidden">
                <img id="lb-img" src="" alt="Fotografija problema" style="width:100%;max-height:70vh;object-fit:contain;background:#0f172a">
                <figcaption style="padding:.85rem 1.1rem">
                    <div id="lb-badge" style="font-weight:700"></div>
                    <div id="lb-date" class="muted" style="font-size:.82rem"></div>
                    <p id="lb-desc" style="margin:.45rem 0 0"></p>
                </figcaption>
            </figure>
            <button id="lb-close" type="button" aria-label="Zatvori pregled"
                style="position:fixed;top:.9rem;right:1rem;width:2.5rem;height:2.5rem;border:0;border-radius:999px;background:#fff;font-size:1.15rem;cursor:pointer">✕</button>
        </div>

        <script>
            (function () {
                const lb = document.getElementById('lb');
                const img = document.getElementById('lb-img');
                const badge = document.getElementById('lb-badge');
                const date = document.getElementById('lb-date');
                const desc = document.getElementById('lb-desc');

                function open(link) {
                    img.src = link.href;
                    badge.textContent = link.dataset.badge || '';
                    date.textContent = link.dataset.date || '';
                    desc.textContent = link.dataset.desc || '';
                    desc.style.display = link.dataset.desc ? '' : 'none';
                    lb.style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                }

                function close() {
                    lb.style.display = 'none';
                    img.src = '';
                    document.body.style.overflow = '';
                }

                document.querySelectorAll('.lb-open').forEach((link) => {
                    link.addEventListener('click', (e) => { e.preventDefault(); open(link); });
                });
                document.getElementById('lb-close').addEventListener('click', close);
                lb.addEventListener('click', (e) => { if (e.target === lb) close(); });
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
            })();
        </script>
    </x-slot:scripts>
</x-tereni.layout>
