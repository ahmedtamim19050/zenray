@php
    $categories = config('zenray.product_categories');
    $products = collect(config('zenray.demo_products'));
@endphp

<x-layouts.app title="Equipment" description="Medical imaging equipment from ZenRay Medical Systems: X-ray systems, digital radiography, imaging accessories and other diagnostic equipment.">
    <x-page-hero eyebrow="Equipment" title="Imaging equipment for every kind of facility"
                 description="Explore X-ray systems, digital radiography, imaging accessories and diagnostic equipment. Our team can help you compare options and prepare a quotation."
                 photo="angio-suite" photo-alt="Ceiling-mounted X-ray system in a procedure room">
        <x-button :href="route('quote')" size="lg" icon="arrow-right" class="btn-shine">Request a Quote</x-button>
        <x-button href="#categories" variant="outline-light" size="lg" class="bg-white/5">Browse categories</x-button>
    </x-page-hero>

    {{-- Category jump links --}}
    <nav id="categories" class="sticky top-[4.5rem] z-30 scroll-mt-24 border-b border-film-600 bg-film-800/90 backdrop-blur-xl" aria-label="Equipment categories">
        <ul class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 py-3 sm:px-6 lg:px-8">
            @foreach ($categories as $category)
                <li><a href="#{{ $category['slug'] }}" class="inline-flex rounded-full bg-film-700 px-4 py-2 text-sm font-semibold whitespace-nowrap text-ink transition hover:bg-glow-400/15 hover:text-glow-100">{{ $category['name'] }}</a></li>
            @endforeach
        </ul>
    </nav>

    @foreach ($categories as $category)
        @php($items = $products->where('category', $category['slug']))
        <section id="{{ $category['slug'] }}" @class(['scroll-mt-36 py-20 sm:py-24', 'bg-film-900' => $loop->even]) aria-labelledby="{{ $category['slug'] }}-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-10 lg:grid-cols-12 lg:gap-14">
                    <div @class(['lg:col-span-5', 'lg:order-2' => $loop->even])>
                        <div data-reveal class="overflow-hidden rounded-[2rem] bg-film-700 shadow-[0_30px_70px_-30px_rgb(11_45_77/0.5)]">
                            <x-photo :name="$category['photo']" :alt="$category['alt']" sizes="(min-width: 1024px) 460px, 100vw" class="aspect-[4/3]" />
                        </div>
                    </div>
                    <div @class(['lg:col-span-7', 'lg:order-1' => $loop->even])>
                        <p class="font-mono text-sm text-signal-400">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} / {{ str_pad(count($categories), 2, '0', STR_PAD_LEFT) }}</p>
                        <h2 id="{{ $category['slug'] }}-heading" data-split class="mt-3 text-4xl font-bold tracking-[-0.035em] text-ink sm:text-5xl">{{ $category['name'] }}</h2>
                        <p class="mt-5 max-w-xl text-lg leading-relaxed text-ink-2" data-reveal>{{ $category['summary'] }}</p>
                        <div class="mt-8 flex flex-wrap gap-3" data-reveal>
                            <x-button :href="route('quote', ['interest' => $category['slug']])" variant="glow" icon="arrow-right" class="btn-shine">Ask about {{ Str::lower($category['name']) }}</x-button>
                            <x-button :href="route('contact', ['interest' => $category['slug']])" variant="outline-dark">Ask a question</x-button>
                        </div>
                    </div>
                </div>

                @if ($items->isNotEmpty())
                    <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3" data-stagger>
                        @foreach ($items as $product)
                            <x-cards.product :product="$product" example />
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endforeach

    <section class="pb-20 sm:pb-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="mb-8 text-sm text-ink-3">Cards marked “Example listing” show the type of equipment we can help with. They are not a list of current stock. Ask our team for exact models, availability and pricing.</p>
            <div class="border-glow rounded-[2rem]">
                <div class="relative isolate overflow-hidden rounded-[2rem] bg-film-900 px-6 py-14 text-center text-white sm:px-12">
                    <div class="hero-aurora pointer-events-none absolute -top-24 left-1/2 -z-10 size-96 -translate-x-1/2 rounded-full bg-signal-500/25 blur-3xl" aria-hidden="true"></div>
                    <h2 data-split class="mx-auto max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">Can’t see exactly what you need?</h2>
                    <p class="mx-auto mt-4 max-w-xl text-navy-200">Tell us about your facility and requirements. We can help you find suitable equipment, even if you are not sure of the product name.</p>
                    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <x-button :href="route('quote')" size="lg" icon="arrow-right" class="btn-shine">Request a Quote</x-button>
                        <x-button :href="route('contact')" variant="outline-light" size="lg">Talk to Our Team</x-button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-product-quick-view />
</x-layouts.app>
