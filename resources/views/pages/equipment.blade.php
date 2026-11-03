@php
    $categories = config('zenray.product_categories');
    $products = collect(config('zenray.demo_products'));
@endphp

<x-layouts.app title="Equipment" description="Medical imaging equipment from ZenRay Medical Systems: X-ray systems, digital radiography, imaging accessories and other diagnostic equipment.">
    <x-page-hero eyebrow="Equipment" title="Imaging equipment for every kind of facility"
                 description="Explore X-ray systems, digital radiography, imaging accessories and diagnostic equipment. Our team can help you compare options and prepare a quotation."
                 photo="angio-suite" photo-alt="Ceiling-mounted X-ray system in a procedure room">
        <x-button :href="route('quote')" size="lg" icon="arrow-right">Request a Quote</x-button>
        @if ($categories)
            <x-button :href="'#'.$categories[0]['slug']" variant="outline-dark" size="lg">Browse categories</x-button>
        @endif
    </x-page-hero>

    {{-- Category tabs: stay under the header and follow the section in view (app.js, [data-section-tabs]) --}}
    <nav id="categories" class="sticky top-16 z-30 border-b border-film-600 bg-film-950" aria-label="Equipment categories" data-section-tabs>
        <ul class="mx-auto flex h-12 max-w-7xl gap-8 overflow-x-auto px-4 sm:px-6 lg:px-8">
            @foreach ($categories as $category)
                <li class="shrink-0">
                    <a href="#{{ $category['slug'] }}" @if ($loop->first) aria-current="true" @endif
                       class="filter-tab relative flex h-full items-center text-sm font-medium whitespace-nowrap text-ink-3 transition-colors duration-300 hover:text-ink aria-[current=true]:text-ink">{{ $category['name'] }}</a>
                </li>
            @endforeach
        </ul>
    </nav>

    @foreach ($categories as $category)
        @php
            $items = $products->where('category', $category['slug'])->values();
            // An odd count starts with a wide tile, so a row never ends with an empty space.
            $wideFirst = $items->count() % 2 === 1;
        @endphp
        <section id="{{ $category['slug'] }}" data-section-tab-target @class(['scroll-mt-28', 'border-t border-film-600' => ! $loop->first]) aria-labelledby="{{ $category['slug'] }}-heading">
            <div class="mx-auto grid max-w-7xl items-start gap-8 px-4 py-14 sm:px-6 sm:py-20 lg:grid-cols-12 lg:gap-12 lg:px-8">
                <div class="lg:sticky lg:top-36 lg:col-span-4" data-reveal>
                    <h2 id="{{ $category['slug'] }}-heading" class="text-2xl leading-tight font-semibold tracking-tight text-ink sm:text-3xl">{{ $category['name'] }}</h2>
                    <p class="mt-4 text-base leading-relaxed text-ink-2">{{ $category['summary'] }}</p>
                    <div class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2">
                        <x-button :href="route('quote', ['interest' => $category['slug']])" icon="arrow-right">Request a Quote</x-button>
                        <a href="{{ route('contact', ['interest' => $category['slug']]) }}" class="group inline-flex min-h-11 items-center text-sm font-semibold text-ink">
                            <span class="link-draw">Ask a question</span>
                        </a>
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:col-span-8" data-stagger>
                    @forelse ($items as $product)
                        <x-cards.product :product="$product" example @class(['sm:col-span-2' => $wideFirst && $loop->first]) />
                    @empty
                        <div class="min-h-[22rem] overflow-hidden rounded-2xl bg-film-700 sm:col-span-2">
                            <x-photo :name="$category['photo']" :alt="$category['alt']" sizes="(min-width: 1024px) 800px, 100vw" />
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    @endforeach

    <section class="border-t border-film-600 py-14 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative isolate flex flex-col gap-8 overflow-hidden rounded-3xl bg-navy-800 p-7 text-white sm:p-10 lg:flex-row lg:items-center lg:justify-between lg:p-12" data-reveal>
                <x-photo name="room-overview" alt="" sizes="(min-width: 1024px) 1200px, 100vw" class="absolute inset-0 -z-20 opacity-30 mix-blend-luminosity" />
                <span class="absolute inset-0 -z-10 bg-linear-to-r from-navy-900 via-navy-800/90 to-navy-800/50" aria-hidden="true"></span>
                <div class="max-w-xl">
                    <h2 class="text-2xl leading-tight font-semibold tracking-tight sm:text-3xl">Can’t see exactly what you need?</h2>
                    <p class="mt-3 text-base leading-relaxed text-navy-100">Tell us about your facility and requirements. We can help you find suitable equipment, even if you are not sure of the product name.</p>
                </div>
                <div class="flex shrink-0 flex-col gap-3 sm:flex-row">
                    <x-button :href="route('quote')" size="lg" icon="arrow-right">Request a Quote</x-button>
                    <x-button :href="route('contact')" variant="outline-light" size="lg">Talk to Our Team</x-button>
                </div>
            </div>
            <p class="mt-6 text-sm text-ink-3">Tiles marked “Example listing” show the type of equipment we can help with. They are not a list of current stock. Ask our team for exact models, availability and pricing.</p>
        </div>
    </section>

    <x-product-quick-view />
</x-layouts.app>
