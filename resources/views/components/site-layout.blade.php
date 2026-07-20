@props(['title' => null, 'description' => null])

@php
    $siteName = $site['site_name'] ?? config('app.name');
    $pageTitle = $title ? "{$title} — {$siteName}" : $siteName;
    $pageDescription = $description ?? __('lawyer.meta.description');
    $locale = app()->getLocale();
    $homeUrl = url($locale);
    $bookingUrl = $site['calendly_url'] ?? null;
    $ctaUrl = $bookingUrl ?: $homeUrl . '#contact';

    $navLinks = [
        ['href' => $homeUrl . '#about', 'label' => __('lawyer.nav.about')],
        ['href' => $homeUrl . '#practice', 'label' => __('lawyer.nav.practice')],
        ['href' => $homeUrl . '#team', 'label' => __('lawyer.nav.team')],
        ['href' => route('blog.index'), 'label' => __('lawyer.nav.blog')],
        ['href' => $homeUrl . '#contact', 'label' => __('lawyer.nav.contact')],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" class="scroll-pt-24">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="/favicon.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-cream-50 font-sans text-ink-900 antialiased">

    {{-- Top bar --}}
    <div class="hidden bg-navy-950 text-navy-100 lg:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-2 text-xs">
            <div class="flex items-center gap-6">
                @if (!empty($site['phone']))
                    <a href="tel:{{ preg_replace('/[^+\d]/', '', $site['phone']) }}"
                        class="flex items-center gap-2 transition hover:text-bronze-400">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 5c0 8.284 6.716 15 15 15l2-4-4.5-2.5-2 2A11.05 11.05 0 0 1 9.5 10.5l2-2L9 4 4 5z" />
                        </svg>
                        {{ $site['phone'] }}
                    </a>
                @endif
                @if (!empty($site['email']))
                    <a href="mailto:{{ $site['email'] }}" class="flex items-center gap-2 transition hover:text-bronze-400">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <path d="m3 7 9 6 9-6" />
                        </svg>
                        {{ $site['email'] }}
                    </a>
                @endif
                @if (!empty($site['working_hours']))
                    <span class="flex items-center gap-2 text-navy-200">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 3" />
                        </svg>
                        {{ $site['working_hours'] }}
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-4">
                @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin_url' => 'LinkedIn'] as $key => $label)
                    @if (!empty($site[$key]))
                        <a href="{{ $site[$key] }}" target="_blank" rel="noopener"
                            class="transition hover:text-bronze-400">{{ $label }}</a>
                    @endif
                @endforeach
                <span class="ml-2 flex items-center gap-1.5 border-l border-navy-700 pl-4 font-medium">
                    <a href="{{ url('sr') }}" class="{{ $locale === 'sr' ? 'text-bronze-400' : 'transition hover:text-bronze-400' }}">SR</a>
                    <span class="text-navy-700">/</span>
                    <a href="{{ url('en') }}" class="{{ $locale === 'en' ? 'text-bronze-400' : 'transition hover:text-bronze-400' }}">EN</a>
                </span>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <header id="site-header" class="sticky top-0 z-40 border-b border-cream-200 bg-white/95 backdrop-blur transition-shadow duration-300">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-4">
            <a href="{{ $homeUrl }}" class="flex items-center gap-3">
                <x-logo class="h-10 w-10 text-bronze-500" />
                <span class="font-display text-xl leading-none text-navy-900 sm:text-2xl">{{ $siteName }}</span>
            </a>

            <nav class="hidden items-center gap-8 lg:flex" aria-label="Main">
                @foreach ($navLinks as $link)
                    <a href="{{ $link['href'] }}"
                        class="text-sm font-medium text-ink-600 transition hover:text-bronze-600">{{ $link['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ $ctaUrl }}" @if ($bookingUrl) target="_blank" rel="noopener" @endif
                    class="hidden rounded-md bg-navy-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-navy-700 sm:inline-block">
                    {{ __('lawyer.header.cta') }}
                </a>

                <button id="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu"
                    class="rounded-md p-2 text-navy-900 transition hover:bg-cream-100 lg:hidden">
                    <span class="sr-only">Menu</span>
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    {{-- Mobile menu --}}
    <div id="menu-overlay" class="pointer-events-none fixed inset-0 z-40 bg-navy-950/60 opacity-0 transition-opacity duration-300 lg:hidden"></div>
    <div id="mobile-menu"
        class="fixed inset-y-0 right-0 z-50 flex w-80 max-w-[85vw] translate-x-full flex-col bg-navy-950 p-8 text-white transition-transform duration-300 lg:hidden">
        <div class="flex items-center justify-between">
            <x-logo class="h-9 w-9 text-bronze-500" />
            <button id="menu-close" type="button" class="rounded-md p-2 transition hover:bg-navy-800">
                <span class="sr-only">Close</span>
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        <nav class="mt-10 flex flex-col gap-1" aria-label="Mobile">
            @foreach ($navLinks as $link)
                <a href="{{ $link['href'] }}"
                    class="rounded-md px-4 py-3 font-display text-lg text-cream-100 transition hover:bg-navy-800 hover:text-bronze-400">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <a href="{{ $ctaUrl }}" @if ($bookingUrl) target="_blank" rel="noopener" @endif
            class="mt-8 rounded-md bg-bronze-500 px-5 py-3 text-center text-sm font-semibold text-white transition hover:bg-bronze-600">
            {{ __('lawyer.header.cta') }}
        </a>

        <div class="mt-auto flex items-center gap-3 pt-8 text-sm font-medium text-navy-100">
            <a href="{{ url('sr') }}" class="{{ $locale === 'sr' ? 'text-bronze-400' : '' }}">Srpski</a>
            <span class="text-navy-700">/</span>
            <a href="{{ url('en') }}" class="{{ $locale === 'en' ? 'text-bronze-400' : '' }}">English</a>
        </div>
    </div>

    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-navy-950 text-navy-100">
        <div class="mx-auto grid max-w-7xl gap-12 px-6 py-16 md:grid-cols-3">
            <div>
                <div class="flex items-center gap-3">
                    <x-logo class="h-9 w-9 text-bronze-500" />
                    <span class="font-display text-xl text-white">{{ $siteName }}</span>
                </div>
                <p class="mt-5 max-w-sm text-sm leading-relaxed">{{ __('lawyer.footer.about') }}</p>
                <div class="mt-6 flex items-center gap-4 text-sm">
                    @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin_url' => 'LinkedIn'] as $key => $label)
                        @if (!empty($site[$key]))
                            <a href="{{ $site[$key] }}" target="_blank" rel="noopener"
                                class="transition hover:text-bronze-400">{{ $label }}</a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-bronze-400">{{ __('lawyer.footer.quick_links') }}</h3>
                <ul class="mt-5 space-y-3 text-sm">
                    <li><a href="{{ $homeUrl }}" class="transition hover:text-bronze-400">{{ __('lawyer.nav.home') }}</a></li>
                    @foreach ($navLinks as $link)
                        <li><a href="{{ $link['href'] }}" class="transition hover:text-bronze-400">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-semibold uppercase tracking-[0.2em] text-bronze-400">{{ __('lawyer.footer.contact') }}</h3>
                <ul class="mt-5 space-y-3 text-sm">
                    @if (!empty($site['address']))
                        <li>{{ $site['address'] }}</li>
                    @endif
                    @if (!empty($site['phone']))
                        <li><a href="tel:{{ preg_replace('/[^+\d]/', '', $site['phone']) }}"
                                class="transition hover:text-bronze-400">{{ $site['phone'] }}</a></li>
                    @endif
                    @if (!empty($site['email']))
                        <li><a href="mailto:{{ $site['email'] }}" class="transition hover:text-bronze-400">{{ $site['email'] }}</a></li>
                    @endif
                    @if (!empty($site['working_hours']))
                        <li>{{ $site['working_hours'] }}</li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="border-t border-navy-800">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-6 py-6 text-xs text-navy-200 sm:flex-row">
                <p>© {{ date('Y') }} {{ $siteName }}. {{ __('lawyer.footer.rights') }}</p>
                <p>{{ __('lawyer.footer.disclaimer') }}</p>
            </div>
        </div>
    </footer>

</body>

</html>
