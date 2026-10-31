<x-layouts.app title="Blog" description="Plain-language guides and news about X-ray, digital radiography and imaging equipment from ZenRay Medical Systems.">
    <x-page-hero eyebrow="Blog" title="Insights on imaging equipment"
                 description="Plain-language guides, tips and news about X-ray systems, digital radiography and keeping imaging equipment running." />

    @php
        $chip = 'inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold whitespace-nowrap transition';
        $chipOn = 'bg-glow-400/15 text-glow-100 ring-1 ring-glow-400/40';
        $chipOff = 'bg-film-800 text-ink-2 hover:text-ink';
    @endphp

    {{-- Topic filter + search --}}
    <div class="sticky top-[4.5rem] z-30 border-b border-glow-400/10 bg-film-950/85 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-3 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <nav class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0" aria-label="Blog topics">
                <ul class="flex w-max gap-2">
                    <li>
                        <a href="{{ route('blog.index', array_filter(['q' => $search])) }}" @if (! $category) aria-current="page" @endif
                           class="{{ $chip }} {{ $category ? $chipOff : $chipOn }}">All topics</a>
                    </li>
                    @foreach ($categories as $item)
                        @php($current = $category?->is($item))
                        <li>
                            <a href="{{ route('blog.index', array_filter(['category' => $item->slug, 'q' => $search])) }}" @if ($current) aria-current="page" @endif
                               class="{{ $chip }} {{ $current ? $chipOn : $chipOff }}">
                                {{ $item->name }}
                                <span class="font-mono text-xs text-ink-3">{{ $item->posts_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <form method="GET" action="{{ route('blog.index') }}" class="flex gap-2" role="search">
                @if ($category) <input type="hidden" name="category" value="{{ $category->slug }}"> @endif
                <label for="blog-search" class="sr-only">Search articles</label>
                <input id="blog-search" type="search" name="q" value="{{ $search }}" placeholder="Search articles…"
                       class="h-10 w-full rounded-full border-0 bg-film-800 px-4 text-sm text-ink ring-1 ring-film-600 placeholder:text-ink-3 focus:ring-2 focus:ring-glow-400 focus:outline-none lg:w-64">
                <button type="submit" class="flex size-10 shrink-0 items-center justify-center rounded-full bg-glow-400/15 text-glow-100 ring-1 ring-glow-400/40 hover:bg-glow-400/25">
                    <x-icon name="inspect" class="size-4" /><span class="sr-only">Search</span>
                </button>
            </form>
        </div>
    </div>

    <section class="py-16 sm:py-20" aria-labelledby="posts-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 id="posts-heading" class="sr-only">{{ $category ? $category->name : 'Latest articles' }}</h2>

            @if ($search !== '')
                <p class="mb-8 text-ink-2">
                    Results for <span class="font-semibold text-ink">“{{ $search }}”</span>{{ $category ? ' in '.$category->name : '' }}.
                    <a href="{{ route('blog.index', array_filter(['category' => $category?->slug])) }}" class="font-semibold text-glow-300 underline underline-offset-4">Clear search</a>
                </p>
            @elseif ($category?->description)
                <p class="mb-8 max-w-2xl text-ink-2">{{ $category->description }}</p>
            @endif

            @if ($featured)
                <div class="mb-8" data-reveal><x-cards.post :post="$featured" featured /></div>
            @endif

            @if ($posts->isNotEmpty())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3" data-stagger>
                    @foreach ($posts as $post)
                        <x-cards.post :post="$post" />
                    @endforeach
                </div>
            @elseif (! $featured)
                <div class="rounded-2xl bg-film-800 px-6 py-16 text-center ring-1 ring-film-600">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-glow-400/10 text-glow-300 ring-1 ring-glow-400/25"><x-icon name="document" class="size-6" /></span>
                    <p class="mt-4 font-bold text-ink">{{ $search !== '' ? 'No articles match your search' : 'No articles yet' }}</p>
                    <p class="mt-1 text-sm text-ink-3">{{ $search !== '' ? 'Try different words or browse all topics.' : 'New articles will appear here soon.' }}</p>
                </div>
            @endif

            @if ($posts->hasPages())
                <div class="mt-12">{{ $posts->links('partials.pagination-dark') }}</div>
            @endif
        </div>
    </section>
</x-layouts.app>
