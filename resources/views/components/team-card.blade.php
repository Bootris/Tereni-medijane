@props(['member'])

@php
    $initials = collect(explode(' ', trim($member->name)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');
@endphp

<article {{ $attributes->merge(['class' => 'group flex h-full flex-col items-center rounded-xl border border-cream-300 bg-white p-8 text-center shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl']) }}>
    @if ($member->photo)
        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($member->photo) }}"
            alt="{{ $member->name }}"
            class="h-28 w-28 rounded-full object-cover ring-4 ring-cream-200 transition duration-300 group-hover:ring-bronze-200"
            loading="lazy">
    @else
        <div class="flex h-28 w-28 items-center justify-center rounded-full bg-navy-900 font-display text-3xl text-bronze-400 ring-4 ring-cream-200 transition duration-300 group-hover:ring-bronze-200">
            {{ $initials }}
        </div>
    @endif

    <h3 class="mt-6 font-display text-xl text-navy-900">{{ $member->name }}</h3>

    @if ($member->title)
        <p class="mt-1 text-sm font-medium uppercase tracking-wide text-bronze-600">{{ $member->title }}</p>
    @endif

    @if ($member->bio)
        <p class="mt-4 line-clamp-4 text-sm leading-relaxed text-ink-600">
            {{ Illuminate\Support\Str::limit(trim(strip_tags($member->bio)), 180) }}
        </p>
    @endif

    <div class="mt-auto flex items-center gap-4 pt-6 text-ink-400">
        @if ($member->email)
            <a href="mailto:{{ $member->email }}" class="transition hover:text-bronze-600" aria-label="Email — {{ $member->name }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="14" rx="2" />
                    <path d="m3 7 9 6 9-6" />
                </svg>
            </a>
        @endif
        @if ($member->phone)
            <a href="tel:{{ preg_replace('/[^+\d]/', '', $member->phone) }}" class="transition hover:text-bronze-600"
                aria-label="{{ __('lawyer.contact.phone') }} — {{ $member->name }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 5c0 8.284 6.716 15 15 15l2-4-4.5-2.5-2 2A11.05 11.05 0 0 1 9.5 10.5l2-2L9 4 4 5z" />
                </svg>
            </a>
        @endif
        @if ($member->linkedin)
            <a href="{{ $member->linkedin }}" target="_blank" rel="noopener" class="transition hover:text-bronze-600"
                aria-label="LinkedIn — {{ $member->name }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M6.5 8.5v11H3v-11h3.5zM4.75 3.5a2 2 0 1 1 0 4 2 2 0 0 1 0-4zM21 13.3v6.2h-3.5v-5.7c0-1.4-.6-2.3-1.9-2.3-1 0-1.6.7-1.9 1.4-.1.2-.1.6-.1.9v5.7H10s.05-9.9 0-11h3.5v1.6c.5-.8 1.4-1.9 3.3-1.9 2.4 0 4.2 1.6 4.2 5.1z" />
                </svg>
            </a>
        @endif
    </div>
</article>
