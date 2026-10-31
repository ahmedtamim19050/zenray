{{-- Blog post card. Pass a Post model; "featured" renders the wide lead layout. --}}
@props(['post', 'featured' => false])

<article {{ $attributes->class([
    'group relative flex flex-col overflow-hidden rounded-2xl bg-film-800 ring-1 ring-film-600 transition duration-300 hover:-translate-y-1 hover:ring-glow-400/40 hover:shadow-[0_30px_60px_-30px_rgb(46_199_240/0.35)]',
    'lg:grid lg:grid-cols-12' => $featured,
]) }}>
    <div @class(['relative overflow-hidden bg-film-700', 'aspect-[16/10]' => ! $featured, 'aspect-[16/10] lg:col-span-7 lg:aspect-auto lg:min-h-[24rem]' => $featured])>
        @if ($post->photo)
            <x-photo :name="$post->photo" :alt="$post->alt ?: $post->title" :sizes="$featured ? '(min-width: 1024px) 700px, 100vw' : '(min-width: 1024px) 400px, (min-width: 640px) 50vw, 100vw'"
                     class="xray-hover absolute inset-0 transition duration-700 ease-out group-hover:scale-[1.03]" />
        @else
            <div class="bg-grid-dark absolute inset-0 flex items-center justify-center" aria-hidden="true">
                <img src="{{ asset('images/zenray-mark-light.svg') }}" alt="" width="160" height="112" class="w-24 opacity-20">
            </div>
        @endif
        @if ($post->category)
            <span class="absolute top-3 left-3 rounded-md bg-film-950/75 px-2.5 py-1 font-mono text-[0.6875rem] font-semibold tracking-wider text-glow-100 uppercase ring-1 ring-glow-400/30 backdrop-blur-md">{{ $post->category->name }}</span>
        @endif
    </div>

    <div @class(['flex flex-1 flex-col p-6', 'lg:col-span-5 lg:justify-center lg:p-10' => $featured])>
        <p class="font-mono text-xs tracking-wider text-ink-3 uppercase">
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j M Y') }}</time>
            <span aria-hidden="true">·</span> {{ $post->readingMinutes() }} min read
        </p>
        <h3 @class(['mt-2 font-bold text-balance text-ink', 'text-lg' => ! $featured, 'text-2xl tracking-tight sm:text-3xl' => $featured])>
            <a href="{{ route('blog.show', $post) }}" class="after:absolute after:inset-0 group-hover:text-glow-100">{{ $post->title }}</a>
        </h3>
        <p @class(['mt-3 leading-relaxed text-ink-2', 'line-clamp-3 text-[0.9375rem]' => ! $featured, 'text-base sm:text-lg' => $featured])>{{ $post->summary() }}</p>
        <span class="mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-semibold text-signal-400" aria-hidden="true">
            Read article <x-icon name="arrow-right" class="size-4 transition-transform group-hover:translate-x-1" />
        </span>
    </div>
</article>
