<x-site-layout :title="$post->seo_title ?: $post->title" :description="$post->seo_description ?: $post->excerpt">

    {{-- Article header --}}
    <section class="relative overflow-hidden bg-navy-950 bg-hero-lines">
        <span class="hero-mark absolute -right-8 -top-16 text-[18rem] text-bronze-500/[0.07]" aria-hidden="true">§</span>
        <div class="relative mx-auto max-w-4xl px-6 py-16 sm:py-20">
            <a href="{{ route('blog.index') }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-navy-100 transition hover:text-bronze-400">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 10H4M8 5l-5 5 5 5" />
                </svg>
                {{ __('lawyer.blog.back') }}
            </a>

            <div class="mt-8 flex flex-wrap items-center gap-3 text-xs text-navy-200">
                @if ($post->category)
                    <span class="rounded-full bg-bronze-500 px-3 py-1 font-semibold uppercase tracking-wide text-white">
                        {{ $post->category->name }}
                    </span>
                @endif
                <time datetime="{{ $post->published_at?->toDateString() }}">
                    {{ __('lawyer.blog.published') }} {{ $post->published_at?->format('d.m.Y') }}
                </time>
                <span aria-hidden="true">·</span>
                <span>{{ $post->reading_time }} {{ __('lawyer.blog.min_read') }}</span>
                @if ($post->author_display)
                    <span aria-hidden="true">·</span>
                    <span>{{ $post->author_display }}</span>
                @endif
            </div>

            <h1 class="mt-5 font-display text-3xl leading-tight text-white text-balance sm:text-5xl">
                {{ $post->title }}
            </h1>

            @if ($post->excerpt)
                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-navy-100">{{ $post->excerpt }}</p>
            @endif
        </div>
    </section>

    {{-- Article body --}}
    <article class="mx-auto max-w-4xl px-6 py-16">
        @if ($post->featured_image)
            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}"
                alt="{{ $post->title }}" class="mb-10 w-full rounded-xl border border-cream-300 shadow-sm">
        @endif

        @if ($post->video_embed_url)
            <div class="mb-10 aspect-video overflow-hidden rounded-xl border border-cream-300 bg-navy-950 shadow-sm">
                <iframe src="{{ $post->video_embed_url }}" class="h-full w-full border-0" loading="lazy"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen title="{{ $post->title }}"></iframe>
            </div>
        @endif

        <div class="prose-law">
            {!! $post->body !!}
        </div>
    </article>

    {{-- Related --}}
    @if ($related->isNotEmpty())
        <section class="border-t border-cream-200 bg-cream-100 py-16">
            <div class="mx-auto max-w-7xl px-6">
                <h2 class="font-display text-2xl text-navy-900">{{ __('lawyer.blog.related') }}</h2>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    @foreach ($related as $relatedPost)
                        <x-post-card :post="$relatedPost" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-site-layout>
