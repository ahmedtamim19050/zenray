@props(['service', 'index' => 0])

<article data-spotlight id="service-{{ $service['slug'] }}" {{ $attributes->class('group flex h-full flex-col rounded-2xl bg-film-800 p-6 ring-1 ring-film-600 transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover sm:p-7') }}>
    <div class="flex items-center justify-between">
        <div class="flex size-11 items-center justify-center rounded-xl bg-glow-400/10 text-glow-300 ring-1 ring-glow-400/25 transition-colors duration-300 group-hover:bg-signal-500 group-hover:text-navy-950">
            <x-icon :name="$service['icon']" class="size-5" />
        </div>
        <span class="font-mono text-xs text-ink-3" aria-hidden="true">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
    </div>

    <h3 class="mt-5 text-lg font-bold text-ink">{{ $service['name'] }}</h3>
    <p class="mt-2 text-[0.9375rem] leading-relaxed text-ink-2">{{ $service['summary'] }}</p>

    <p class="mt-4 rounded-lg bg-film-900 px-3 py-2.5 text-sm text-ink-2">
        <span class="font-semibold text-ink">Next step:</span> {{ $service['next_step'] }}
    </p>

    <a href="{{ route('contact', ['interest' => $service['slug']]) }}" class="mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-semibold text-ink hover:text-signal-300">
        {{ $service['action'] }}
        <x-icon name="arrow-right" class="size-4 text-signal-400 transition-transform duration-200 group-hover:translate-x-1" />
    </a>
</article>
