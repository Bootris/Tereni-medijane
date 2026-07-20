<x-site-layout>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-navy-950 bg-hero-lines">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-navy-900 via-navy-950 to-navy-950"></div>
        <span class="hero-mark absolute -right-10 -top-24 select-none text-[26rem] text-bronze-500/[0.07] sm:-right-4 sm:text-[32rem]"
            aria-hidden="true">§</span>

        <div class="relative mx-auto max-w-7xl px-6 py-24 sm:py-32">
            <div class="reveal is-visible max-w-3xl">
                <p class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.3em] text-bronze-400">
                    <span class="h-px w-10 bg-bronze-500" aria-hidden="true"></span>
                    {{ __('lawyer.hero.eyebrow') }}
                </p>
                <h1 class="mt-6 font-display text-4xl leading-[1.15] text-white text-balance sm:text-6xl">
                    {{ __('lawyer.hero.title_1') }}
                    <span class="text-bronze-400">{{ __('lawyer.hero.title_accent') }}</span>
                    {{ __('lawyer.hero.title_2') }}
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-navy-100">
                    {{ __('lawyer.hero.text') }}
                </p>
                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="#contact"
                        class="rounded-md bg-bronze-500 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-bronze-500/20 transition hover:bg-bronze-600">
                        {{ __('lawyer.hero.cta_primary') }}
                    </a>
                    <a href="#practice"
                        class="rounded-md border border-navy-700 px-7 py-3.5 text-sm font-semibold text-navy-100 transition hover:border-bronze-400 hover:text-bronze-400">
                        {{ __('lawyer.hero.cta_secondary') }}
                    </a>
                </div>
            </div>

            {{-- Stats --}}
            <div class="reveal-group mt-20 grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-navy-800 bg-navy-800 lg:grid-cols-4">
                @foreach (__('lawyer.stats') as $stat)
                    <div class="bg-navy-900/80 px-6 py-6 text-center backdrop-blur">
                        <p class="font-display text-3xl text-bronze-400">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-xs font-medium uppercase tracking-wider text-navy-200">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- About --}}
    <section id="about" class="mx-auto max-w-7xl px-6 py-24">
        <div class="grid items-start gap-14 lg:grid-cols-2">
            <div class="reveal">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-bronze-600">{{ __('lawyer.about.eyebrow') }}</p>
                <h2 class="mt-3 font-display text-3xl leading-tight text-navy-900 text-balance sm:text-4xl">
                    {{ __('lawyer.about.title') }}
                </h2>
                <div class="mt-5 h-0.5 w-14 bg-bronze-500"></div>
                <p class="mt-6 text-base leading-relaxed text-ink-600">{{ __('lawyer.about.text') }}</p>
            </div>

            <div class="reveal-group grid gap-6">
                @foreach (__('lawyer.about.points') as $index => $point)
                    <div class="flex gap-5 rounded-xl border border-cream-300 bg-white p-6 shadow-sm">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-bronze-100 font-display text-lg text-bronze-700">
                            {{ sprintf('%02d', $index + 1) }}
                        </span>
                        <div>
                            <h3 class="font-display text-lg text-navy-900">{{ $point['title'] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-ink-600">{{ $point['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Practice areas --}}
    <section id="practice" class="border-y border-cream-200 bg-cream-100 py-24">
        <div class="mx-auto max-w-7xl px-6">
            <x-section-heading :eyebrow="__('lawyer.practice.eyebrow')" :title="__('lawyer.practice.title')"
                :subtitle="__('lawyer.practice.subtitle')" />

            @php
                $practiceIcons = [
                    'business' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18"/>',
                    'civil' => '<path d="M12 3v18M5 21h14M9 3.5h6"/><path d="M7 7 4 13a3.5 3.5 0 0 0 6 0L7 7zM17 7l-3 6a3.5 3.5 0 0 0 6 0l-3-6z"/>',
                    'criminal' => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3z"/><path d="m9 12 2 2 4-4"/>',
                    'family' => '<path d="M12 20s-7-4.5-9-9a4.8 4.8 0 0 1 9-2.2A4.8 4.8 0 0 1 21 11c-2 4.5-9 9-9 9z"/>',
                    'labor' => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><circle cx="17" cy="9" r="2.6"/><path d="M15.5 20a5 5 0 0 1 5-4.8"/>',
                    'real_estate' => '<path d="m3 11 9-7 9 7"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
                ];
            @endphp

            <div class="reveal-group mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (__('lawyer.practice.areas') as $key => $area)
                    <div class="group rounded-xl border border-cream-300 bg-white p-8 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-bronze-300 hover:shadow-xl">
                        <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-navy-900 text-bronze-400 transition duration-300 group-hover:bg-bronze-500 group-hover:text-white">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                {!! $practiceIcons[$key] ?? $practiceIcons['civil'] !!}
                            </svg>
                        </span>
                        <h3 class="mt-6 font-display text-xl text-navy-900">{{ $area['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-ink-600">{{ $area['text'] }}</p>
                        <div class="mt-6 h-0.5 w-0 bg-bronze-500 transition-all duration-300 group-hover:w-12"></div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Team --}}
    @if ($team->isNotEmpty())
        <section id="team" class="mx-auto max-w-7xl px-6 py-24">
            <x-section-heading :eyebrow="__('lawyer.team.eyebrow')" :title="__('lawyer.team.title')"
                :subtitle="__('lawyer.team.subtitle')" />

            <div class="reveal-group mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-{{ min(4, max(2, $team->count())) }}">
                @foreach ($team as $member)
                    <x-team-card :member="$member" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Latest articles --}}
    @if ($latestPosts->isNotEmpty())
        <section class="border-y border-cream-200 bg-cream-100 py-24">
            <div class="mx-auto max-w-7xl px-6">
                <x-section-heading :eyebrow="__('lawyer.blog.eyebrow')" :title="__('lawyer.blog.title')"
                    :subtitle="__('lawyer.blog.subtitle')" />

                <div class="reveal-group mt-14 grid gap-6 md:grid-cols-3">
                    @foreach ($latestPosts as $post)
                        <x-post-card :post="$post" />
                    @endforeach
                </div>

                <div class="reveal mt-12 text-center">
                    <a href="{{ route('blog.index') }}"
                        class="inline-flex items-center gap-2 rounded-md border border-navy-900 px-7 py-3 text-sm font-semibold text-navy-900 transition hover:bg-navy-900 hover:text-white">
                        {{ __('lawyer.blog.view_all') }}
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 10h13M12 5l5 5-5 5" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- CTA band --}}
    <section class="relative overflow-hidden bg-navy-950 bg-hero-lines">
        <span class="hero-mark absolute -left-8 top-1/2 -translate-y-1/2 text-[18rem] text-bronze-500/[0.06]" aria-hidden="true">§</span>
        <div class="relative mx-auto flex max-w-7xl flex-col items-center gap-8 px-6 py-20 text-center">
            <h2 class="reveal max-w-2xl font-display text-3xl leading-tight text-white text-balance sm:text-4xl">
                {{ __('lawyer.cta.title') }}
            </h2>
            <p class="reveal max-w-xl text-navy-100">{{ __('lawyer.cta.text') }}</p>
            <div class="reveal flex flex-wrap items-center justify-center gap-4">
                <a href="{{ ($site['calendly_url'] ?? null) ?: '#contact' }}"
                    @if (!empty($site['calendly_url'])) target="_blank" rel="noopener" @endif
                    class="rounded-md bg-bronze-500 px-8 py-3.5 text-sm font-semibold text-white shadow-lg shadow-bronze-500/20 transition hover:bg-bronze-600">
                    {{ __('lawyer.cta.button') }}
                </a>
                <a href="#contact" class="text-sm font-medium text-navy-100 underline decoration-bronze-500 underline-offset-4 transition hover:text-bronze-400">
                    {{ __('lawyer.cta.or_write') }}
                </a>
            </div>
        </div>
    </section>

    {{-- Contact --}}
    <section id="contact" class="mx-auto max-w-7xl px-6 py-24">
        <x-section-heading :eyebrow="__('lawyer.contact.eyebrow')" :title="__('lawyer.contact.title')"
            :subtitle="__('lawyer.contact.subtitle')" />

        <div class="mt-14 grid gap-10 lg:grid-cols-5">
            {{-- Form --}}
            <div class="reveal lg:col-span-3">
                <div class="rounded-xl border border-cream-300 bg-white p-8 shadow-sm sm:p-10">
                    @if (session('contact_success'))
                        <div class="mb-6 flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800"
                            role="status">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" />
                                <path d="m8.5 12.5 2.5 2.5 5-5.5" />
                            </svg>
                            {{ __('lawyer.contact.form.success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.submit') }}" class="grid gap-5 sm:grid-cols-2">
                        @csrf

                        {{-- Honeypot --}}
                        <div class="absolute -left-[9999px]" aria-hidden="true">
                            <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                        </div>

                        <div>
                            <label for="contact-name" class="mb-1.5 block text-sm font-medium text-navy-900">{{ __('lawyer.contact.form.name') }} *</label>
                            <input id="contact-name" type="text" name="name" value="{{ old('name') }}" required
                                class="w-full rounded-md border border-cream-300 bg-cream-50 px-4 py-2.5 text-sm outline-none transition focus:border-bronze-500 focus:ring-2 focus:ring-bronze-200">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact-email" class="mb-1.5 block text-sm font-medium text-navy-900">{{ __('lawyer.contact.form.email') }} *</label>
                            <input id="contact-email" type="email" name="email" value="{{ old('email') }}" required
                                class="w-full rounded-md border border-cream-300 bg-cream-50 px-4 py-2.5 text-sm outline-none transition focus:border-bronze-500 focus:ring-2 focus:ring-bronze-200">
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact-phone" class="mb-1.5 block text-sm font-medium text-navy-900">{{ __('lawyer.contact.form.phone') }}</label>
                            <input id="contact-phone" type="tel" name="phone" value="{{ old('phone') }}"
                                class="w-full rounded-md border border-cream-300 bg-cream-50 px-4 py-2.5 text-sm outline-none transition focus:border-bronze-500 focus:ring-2 focus:ring-bronze-200">
                        </div>

                        <div>
                            <label for="contact-subject" class="mb-1.5 block text-sm font-medium text-navy-900">{{ __('lawyer.contact.form.subject') }}</label>
                            <input id="contact-subject" type="text" name="subject" value="{{ old('subject') }}"
                                class="w-full rounded-md border border-cream-300 bg-cream-50 px-4 py-2.5 text-sm outline-none transition focus:border-bronze-500 focus:ring-2 focus:ring-bronze-200">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="contact-message" class="mb-1.5 block text-sm font-medium text-navy-900">{{ __('lawyer.contact.form.message') }} *</label>
                            <textarea id="contact-message" name="message" rows="5" required
                                class="w-full rounded-md border border-cream-300 bg-cream-50 px-4 py-2.5 text-sm outline-none transition focus:border-bronze-500 focus:ring-2 focus:ring-bronze-200">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <button type="submit"
                                class="w-full rounded-md bg-navy-900 px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-navy-700 sm:w-auto">
                                {{ __('lawyer.contact.form.submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Info --}}
            <div class="reveal lg:col-span-2">
                <div class="flex h-full flex-col overflow-hidden rounded-xl bg-navy-950 text-navy-100">
                    <div class="p-8 sm:p-10">
                        <h3 class="font-display text-xl text-white">{{ __('lawyer.contact.info_title') }}</h3>
                        <ul class="mt-6 space-y-5 text-sm">
                            @if (!empty($site['address']))
                                <li class="flex gap-3">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-bronze-400" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z" />
                                        <circle cx="12" cy="10" r="2.5" />
                                    </svg>
                                    <span><span class="block text-xs uppercase tracking-wider text-navy-200">{{ __('lawyer.contact.address') }}</span>{{ $site['address'] }}</span>
                                </li>
                            @endif
                            @if (!empty($site['phone']))
                                <li class="flex gap-3">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-bronze-400" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M4 5c0 8.284 6.716 15 15 15l2-4-4.5-2.5-2 2A11.05 11.05 0 0 1 9.5 10.5l2-2L9 4 4 5z" />
                                    </svg>
                                    <span><span class="block text-xs uppercase tracking-wider text-navy-200">{{ __('lawyer.contact.phone') }}</span>
                                        <a href="tel:{{ preg_replace('/[^+\d]/', '', $site['phone']) }}" class="transition hover:text-bronze-400">{{ $site['phone'] }}</a></span>
                                </li>
                            @endif
                            @if (!empty($site['email']))
                                <li class="flex gap-3">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-bronze-400" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="5" width="18" height="14" rx="2" />
                                        <path d="m3 7 9 6 9-6" />
                                    </svg>
                                    <span><span class="block text-xs uppercase tracking-wider text-navy-200">{{ __('lawyer.contact.email') }}</span>
                                        <a href="mailto:{{ $site['email'] }}" class="transition hover:text-bronze-400">{{ $site['email'] }}</a></span>
                                </li>
                            @endif
                            @if (!empty($site['working_hours']))
                                <li class="flex gap-3">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-bronze-400" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 7v5l3 3" />
                                    </svg>
                                    <span><span class="block text-xs uppercase tracking-wider text-navy-200">{{ __('lawyer.contact.hours') }}</span>{{ $site['working_hours'] }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>

                    @if (!empty($site['map_embed']))
                        <iframe src="{{ $site['map_embed'] }}" class="mt-auto h-56 w-full border-0 grayscale"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map"></iframe>
                    @endif
                </div>
            </div>
        </div>
    </section>

</x-site-layout>
