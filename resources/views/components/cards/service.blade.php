@props(['service', 'index' => 0])

<article id="service-{{ $service['slug'] }}" {{ $attributes->class('group flex h-full flex-col rounded-2xl bg-film-800 p-6 ring-1 ring-film-600 transition-shadow duration-200 hover:shadow-card-hover sm:p-7') }}>
    <div class="flex size-11 items-center justify-center rounded-xl bg-glow-400/10 text-glow-300 ring-1 ring-glow-400/25">
            <x-icon :name="$service['icon']" class="size-5" />
        </div>

    <h3 class="mt-5 text-lg font-bold text-ink">{{ $service['name'] }}</h3>
    <p class="mt-2 text-base leading-relaxed text-ink-2">{{ $service['summary'] }}</p>

    <p class="mt-4 rounded-lg bg-film-900 px-3 py-2.5 text-sm text-ink-2">
        <span class="font-semibold text-ink">Next step:</span> {{ $service['next_step'] }}
    </p>

    <a href="{{ route('contact', ['interest' => $service['slug']]) }}" class="mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-semibold text-ink underline-offset-4 hover:underline">
        {{ $service['action'] }}
        <x-icon name="arrow-right" class="size-4 text-signal-text transition-transform duration-200 group-hover:translate-x-0.5" />
    </a>
</article>
