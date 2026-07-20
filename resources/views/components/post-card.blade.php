@props(['post'])

<article {{ $attributes->merge(['class' => 'group flex h-full flex-col overflow-hidden rounded-xl border border-cream-300 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl']) }}>
    <a href="{{ route('blog.show', $post->slug) }}" class="relative block aspect-[16/9] overflow-hidden">
        @if ($post->featured_image)
            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}"
                alt="{{ $post->title }}"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-navy-900 via-navy-800 to-navy-700">
                <x-logo class="h-14 w-14 text-bronze-500/40 transition duration-500 group-hover:scale-110" />
            </div>
        @endif
        @if ($post->category)
            <span class="absolute left-4 top-4 rounded-full bg-bronze-500 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white">
                {{ $post->category->name }}
            </span>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-6">
        <div class="flex items-center gap-3 text-xs text-ink-400">
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('d.m.Y') }}</time>
            <span aria-hidden="true">·</span>
            <span>{{ $post->reading_time }} {{ __('lawyer.blog.min_read') }}</span>
        </div>

        <h3 class="mt-3 font-display text-xl leading-snug text-navy-900">
            <a href="{{ route('blog.show', $post->slug) }}" class="transition hover:text-bronze-600">
                {{ $post->title }}
            </a>
        </h3>

        @if ($post->excerpt)
            <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-ink-600">{{ $post->excerpt }}</p>
        @endif

        <a href="{{ route('blog.show', $post->slug) }}"
            class="mt-auto inline-flex items-center gap-2 pt-5 text-sm font-semibold text-bronze-600 transition group-hover:gap-3 hover:text-bronze-700">
            {{ __('lawyer.blog.read_more') }}
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 10h13M12 5l5 5-5 5" />
            </svg>
        </a>
    </div>
</article>
