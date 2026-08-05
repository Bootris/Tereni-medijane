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

                @if ($report->photoUrl())
                    <a href="{{ $report->photoUrl() }}" target="_blank" rel="noopener">
                        <img src="{{ $report->photoUrl() }}" alt="" loading="lazy"
                            style="max-height:220px;border-radius:.5rem;border:1px solid var(--line);margin:.3rem 0">
                    </a>
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
            </article>
        @empty
            <p class="muted">Još nema javnih prijava za ovaj teren. Budi prvi.</p>
        @endforelse
    </section>
</x-tereni.layout>
