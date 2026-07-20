@props(['eyebrow' => null, 'title', 'subtitle' => null, 'align' => 'center', 'dark' => false])

<div {{ $attributes->merge(['class' => 'reveal max-w-2xl ' . ($align === 'center' ? 'mx-auto text-center' : '')]) }}>
    @if ($eyebrow)
        <p class="text-xs font-semibold uppercase tracking-[0.25em] {{ $dark ? 'text-bronze-400' : 'text-bronze-600' }}">
            {{ $eyebrow }}
        </p>
    @endif
    <h2 class="mt-3 font-display text-3xl leading-tight text-balance sm:text-4xl {{ $dark ? 'text-white' : 'text-navy-900' }}">
        {{ $title }}
    </h2>
    @if ($subtitle)
        <p class="mt-4 text-base leading-relaxed {{ $dark ? 'text-navy-100' : 'text-ink-600' }}">
            {{ $subtitle }}
        </p>
    @endif
    <div class="mt-5 h-0.5 w-14 bg-bronze-500 {{ $align === 'center' ? 'mx-auto' : '' }}"></div>
</div>
