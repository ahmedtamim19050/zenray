{{--
    Project card for published, approved-for-publication projects.
    Expects the array shape from App\Support\SiteContent: title, slug, photo,
    image_alt, delivered, description and optional location.
--}}
@props(['project', 'featured' => false])

<article {{ $attributes->class(['group relative overflow-hidden rounded-2xl bg-film-800 ring-1 ring-film-600 transition duration-300 hover:ring-glow-400/40']) }}>
    <div @class(['relative overflow-hidden bg-film-700', 'aspect-[16/10]' => ! $featured, 'aspect-[16/10] lg:aspect-[16/9]' => $featured])>
        @if ($project['photo'])
            <x-photo :name="$project['photo']" :alt="$project['image_alt'] ?? $project['title']" sizes="(min-width: 1024px) 50vw, 100vw"
                     class="xray-hover absolute inset-0 transition duration-700 ease-out group-hover:scale-[1.03]" />
        @endif
    </div>
    <div class="p-6">
        <p class="font-mono text-xs font-semibold tracking-wide text-glow-300 uppercase">{{ $project['delivered'] }}</p>
        <h3 class="mt-1.5 text-lg font-bold text-ink">
            <a href="{{ route('portfolio.show', $project['slug']) }}" class="after:absolute after:inset-0 hover:text-signal-300">{{ $project['title'] }}</a>
        </h3>
        @if (! empty($project['location']))
            <p class="mt-1 flex items-center gap-1.5 text-sm text-ink-3"><x-icon name="map-pin" class="size-4" />{{ $project['location'] }}</p>
        @endif
        <p class="mt-3 text-[0.9375rem] leading-relaxed text-ink-2">{{ $project['description'] }}</p>
    </div>
</article>
