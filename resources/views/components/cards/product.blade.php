{{--
    Product tile: a full photo with its text on top. Accepts an array today; when products
    move to the database, pass a Product model's attributes. Only verified data should be
    supplied. The whole tile opens a quick view with the full details (it falls back to a
    link). The summary slides in on hover or focus, and is always shown on touch screens.
--}}
@props(['product', 'example' => false])

@php
    $categoryName = collect(config('zenray.product_categories'))->firstWhere('slug', $product['category'])['name'] ?? '';
    $large = \App\Support\Photo::url($product['photo'] ?? null);
    $example = $product['example'] ?? $example;
    $quoteUrl = route('quote', ['interest' => $product['category']]);
@endphp

<article {{ $attributes->class('group relative isolate flex h-full min-h-[22rem] flex-col justify-end overflow-hidden rounded-2xl bg-film-700 text-white') }}
         data-filter-item="{{ $product['category'] }}">
    <x-photo :name="$product['photo']" :alt="$product['alt']"
             sizes="(min-width: 1024px) 50vw, (min-width: 640px) 50vw, 100vw"
             class="media-settle absolute inset-0 -z-20 transition-[scale] duration-700 ease-calm motion-safe:group-hover:scale-105" />
    {{-- Keeps white text readable on any photo; the second layer deepens it when the summary shows --}}
    <span class="absolute inset-0 -z-10 bg-linear-to-t from-black/90 via-black/65 via-45% to-transparent to-85%" aria-hidden="true"></span>
    <span class="tile-shade absolute inset-0 -z-10 bg-black/55" aria-hidden="true"></span>

    {{-- The arrow turns to point forward and fills orange, like the What we offer tiles --}}
    <span class="absolute top-4 right-4 flex size-10 items-center justify-center rounded-full bg-black/45 ring-1 ring-white/40 backdrop-blur-md transition-colors duration-300 group-hover:bg-signal-500 group-hover:text-navy-950 group-hover:ring-signal-500 group-focus-within:bg-signal-500 group-focus-within:text-navy-950" aria-hidden="true">
        <x-icon name="arrow-right" class="size-4 -rotate-45 transition-transform duration-300 ease-calm group-hover:rotate-0 group-focus-within:rotate-0" />
    </span>

    {{-- Covers the whole tile. "Get a quote" sits above it. --}}
    <a href="{{ $product['url'] ?? route('products.index') }}"
       class="absolute inset-0 rounded-2xl -outline-offset-4 focus-visible:outline-white"
       data-quick-view
       data-name="{{ $product['name'] }}"
       data-category="{{ $categoryName }}"
       data-summary="{{ $product['summary'] }}"
       data-features="{{ json_encode($product['features'] ?? []) }}"
       data-availability="{{ $product['availability'] ?? 'Availability and pricing on request.' }}"
       data-image="{{ $large }}"
       data-alt="{{ $product['alt'] }}"
       data-quote="{{ $quoteUrl }}"
       data-example="{{ $example ? '1' : '0' }}">
        <span class="sr-only">View details: {{ $product['name'] }}</span>
    </a>

    <div class="p-6">
        <p class="text-xs font-medium text-white">
            {{ $categoryName }}@if ($example) <span aria-hidden="true">·</span> Example listing @endif
        </p>
        <h3 class="mt-1.5 text-xl leading-snug font-semibold tracking-tight"><span class="link-draw">{{ $product['name'] }}</span></h3>

        <div class="tile-reveal">
            <p class="min-h-0 overflow-hidden text-sm leading-relaxed text-white">{{ $product['summary'] }}</p>
        </div>

        <a href="{{ $quoteUrl }}" class="relative z-10 mt-4 inline-flex min-h-11 items-center rounded-full bg-white/15 px-5 text-sm font-semibold ring-1 ring-white/40 backdrop-blur-md transition-colors duration-200 hover:bg-signal-500 hover:text-navy-950 hover:ring-signal-500 focus-visible:outline-white">
            Get a quote<span class="sr-only"> for {{ $product['name'] }}</span>
        </a>
    </div>
</article>
