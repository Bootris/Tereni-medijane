<x-tereni.layout :title="$court->name" :description="'Prijavi stanje terena: ' . $court->name . ($court->facility ? ' — ' . $court->facility->name : '')">
    <p style="margin:0 0 .3rem"><a href="{{ route('tereni.map') }}" class="muted">← Mapa terena</a></p>

    <h1 style="margin:.1rem 0 .4rem;font-size:1.7rem">{{ $court->name }}</h1>
    @if ($court->facility)
        <p class="muted" style="margin:0 0 .6rem">
            {{ $court->facility->name }}@if ($court->facility->address) · {{ $court->facility->address }}@endif
        </p>
    @endif

    <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem">
        <span class="badge badge-gray">{{ $court->type->label() }}</span>
        <span class="badge badge-{{ $court->access->color() }}">{{ $court->access->label() }}</span>
        @if ($court->surface)<span class="badge badge-gray">Podloga: {{ $court->surface }}</span>@endif
        @if ($court->dimensions)<span class="badge badge-gray">{{ $court->dimensions }}</span>@endif
        <span class="badge badge-gray">{{ $court->has_lighting ? 'Osvetljenje: da' : 'Osvetljenje: ne' }}</span>
    </div>

    @if ($court->description)
        <p>{{ $court->description }}</p>
    @endif

    @if (!empty($court->gallery))
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.5rem;margin:1rem 0">
            @foreach ($court->gallery as $image)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}"
                    alt="" loading="lazy"
                    style="width:100%;height:110px;object-fit:cover;border-radius:.5rem;border:1px solid var(--line)">
            @endforeach
        </div>
    @endif

    {{-- Report form --}}
    <section class="card" style="padding:1.25rem;margin:1.5rem 0">
        <h2 style="margin:0 0 .25rem;font-size:1.2rem">Prijavi problem</h2>
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

            <div style="display:grid;gap:1rem;grid-template-columns:1fr 1fr">
                <div style="grid-column:1/-1">
                    <label for="category">Tip problema *</label>
                    <select id="category" name="category" required>
                        <option value="">— izaberi —</option>
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
        <h2 style="font-size:1.2rem">Prijave za ovaj teren ({{ $reports->count() }})</h2>

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

                        {{-- Status timeline — the public pressure. --}}
                        @if ($report->statusChanges->isNotEmpty())
                            <ol style="list-style:none;padding:0;margin:.6rem 0 0;border-left:2px solid var(--line)">
                                @foreach ($report->statusChanges as $change)
                                    <li style="padding:.15rem 0 .45rem .8rem;position:relative">
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
        {{-- Lightbox: full-size photo + the exact problem (category, status, when, description). --}}
        <div id="lb" role="dialog" aria-modal="true" aria-label="Pregled prijave"
            style="display:none;position:fixed;inset:0;z-index:60;background:rgba(15,23,42,.88);padding:1rem;align-items:center;justify-content:center">
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
