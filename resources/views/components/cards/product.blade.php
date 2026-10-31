{{--
    Product card. Accepts an array today; when products move to the database,
    pass a Product model's attributes. Only verified data should be supplied:
    features, availability and price are optional and are simply not shown
    when missing. "View Details" opens a quick view (falls back to a link).
--}}
@props(['product', 'example' => false])

@php
    $features = $product['features'] ?? [];
    $categoryName = collect(config('zenray.product_categories'))->firstWhere('slug', $product['category'])['name'] ?? '';
    $large = \App\Support\Photo::url($product['photo'] ?? null);
    $example = $product['example'] ?? $example;
    $quoteUrl = route('quote', ['interest' => $product['category']]);
@endphp

<article data-spotlight {{ $attributes->class('group relative flex h-full flex-col overflow-hidden rounded-2xl bg-film-800 ring-1 ring-film-600 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover') }}
         data-filter-item="{{ $product['category'] }}">
    <div class="relative aspect-[4/3] overflow-hidden bg-film-700">
        <x-photo :name="$product['photo']" :alt="$product['alt']"
                 sizes="(min-width: 1280px) 400px, (min-width: 640px) 50vw, 100vw"
                 class="xray-hover transition duration-700 ease-out group-hover:scale-[1.03]" />

        @if ($example)
            <span class="absolute top-3 left-3 rounded-full bg-film-800/95 px-2.5 py-1 text-[0.6875rem] font-semibold tracking-wide text-ink uppercase shadow-sm">
                Example listing
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-6">
        <p class="text-xs font-semibold tracking-wide text-signal-400 uppercase">{{ $categoryName }}</p>
        <h3 class="mt-1.5 text-lg font-bold text-ink">{{ $product['name'] }}</h3>
        <p class="mt-2 text-[0.9375rem] leading-relaxed text-ink-2">{{ $product['summary'] }}</p>

        @if ($features)
            <ul class="mt-4 space-y-1.5 text-sm text-ink-2">
                @foreach (array_slice($features, 0, 3) as $feature)
                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 size-4 shrink-0 text-signal-400" />{{ $feature }}</li>
                @endforeach
            </ul>
        @endif

        <p class="mt-4 text-sm text-ink-3">{{ $product['availability'] ?? 'Availability and pricing on request.' }}</p>

        <div class="mt-auto grid grid-cols-2 gap-2 pt-6">
            <a href="{{ $product['url'] ?? route('products.index') }}"
               class="inline-flex h-10 items-center justify-center rounded-full border border-film-600 px-3 text-sm font-semibold text-ink transition hover:border-glow-400"
               data-quick-view
               data-name="{{ $product['name'] }}"
               data-category="{{ $categoryName }}"
               data-summary="{{ $product['summary'] }}"
               data-image="{{ $large }}"
               data-alt="{{ $product['alt'] }}"
               data-quote="{{ $quoteUrl }}"
               data-example="{{ $example ? '1' : '0' }}">
                View Details<span class="sr-only">: {{ $product['name'] }}</span>
            </a>
            <a href="{{ $quoteUrl }}" class="inline-flex h-10 items-center justify-center rounded-full bg-signal-500 px-3 text-sm font-semibold text-navy-950 transition hover:bg-signal-400">
                Get a Quote<span class="sr-only"> for {{ $product['name'] }}</span>
            </a>
        </div>
    </div>
</article>
