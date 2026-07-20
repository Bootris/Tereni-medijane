<x-site-layout :title="__('lawyer.blog.title')" :description="__('lawyer.blog.subtitle')">

    {{-- Page header --}}
    <section class="relative overflow-hidden bg-navy-950 bg-hero-lines">
        <span class="hero-mark absolute -right-8 -top-16 text-[18rem] text-bronze-500/[0.07]" aria-hidden="true">§</span>
        <div class="relative mx-auto max-w-7xl px-6 py-16 sm:py-20">
            <p class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.3em] text-bronze-400">
                <span class="h-px w-10 bg-bronze-500" aria-hidden="true"></span>
                {{ __('lawyer.blog.eyebrow') }}
            </p>
            <h1 class="mt-4 font-display text-4xl leading-tight text-white text-balance sm:text-5xl">
                {{ __('lawyer.blog.title') }}
            </h1>
            <p class="mt-4 max-w-xl text-navy-100">{{ __('lawyer.blog.subtitle') }}</p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-6 py-16">

        {{-- Category filter --}}
        @if ($categories->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('blog.index') }}"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $activeCategory ? 'border border-cream-300 bg-white text-ink-600 hover:border-bronze-400 hover:text-bronze-600' : 'bg-navy-900 text-white' }}">
                    {{ __('lawyer.blog.all') }}
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('blog.index', ['category' => $category->slug]) }}"
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $activeCategory === $category->slug ? 'bg-navy-900 text-white' : 'border border-cream-300 bg-white text-ink-600 hover:border-bronze-400 hover:text-bronze-600' }}">
                        {{ $category->name }}
                        <span class="text-xs opacity-60">({{ $category->posts_count }})</span>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($posts->isEmpty())
            <div class="mt-16 rounded-xl border border-dashed border-cream-300 bg-white p-16 text-center">
                <x-logo class="mx-auto h-12 w-12 text-cream-300" />
                <p class="mt-4 text-ink-600">{{ __('lawyer.blog.empty') }}</p>
            </div>
        @else
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>

            <div class="mt-12">
                {{ $posts->links() }}
            </div>
        @endif
    </section>

</x-site-layout>
