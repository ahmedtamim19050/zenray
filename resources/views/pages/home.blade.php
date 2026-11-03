<x-layouts.app>
    <x-home.hero />

    <x-home.intro />

    {{-- ============================================================
         Featured equipment (filterable)
         ============================================================ --}}
    <section id="featured-equipment" class="scroll-mt-20 py-16 sm:py-24" aria-labelledby="products-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <x-section-heading dark id="products-heading" eyebrow="Equipment line-up" title="Find the right equipment">
                    <x-slot:description>
                        Explore equipment options for your facility and contact our team for product details and pricing.
                    </x-slot:description>
                </x-section-heading>
                <a href="{{ route('quote') }}" class="group inline-flex min-h-11 shrink-0 items-center gap-2 self-start text-sm font-semibold text-ink lg:self-auto">
                    <span class="link-draw">Ask about a specific model</span>
                    <x-icon name="arrow-right" class="size-4 transition-transform duration-300 ease-calm group-hover:translate-x-1" />
                </a>
            </div>

            @php
                $counts = collect($products)->countBy('category');
                $filters = ['all' => ['All equipment', count($products)]]
                    + collect($categories)->mapWithKeys(fn ($c) => [$c['slug'] => [$c['name'], $counts[$c['slug']] ?? 0]])->all();
            @endphp
            {{-- Filter tabs: the orange line draws under the chosen one, like the hero tabs --}}
            <div class="-mx-4 mt-10 overflow-x-auto px-4 sm:mx-0 sm:px-0" role="group" aria-label="Filter equipment by category" data-filter-group>
                <div class="flex min-w-max gap-8 border-b border-film-600">
                    @foreach ($filters as $slug => [$label, $count])
                        <button type="button" data-filter="{{ $slug }}" aria-pressed="{{ $slug === 'all' ? 'true' : 'false' }}"
                                class="filter-tab relative inline-flex min-h-11 items-center gap-1.5 text-sm font-medium whitespace-nowrap text-ink-3 transition-colors duration-300 hover:text-ink aria-pressed:text-ink">
                            {{ $label }}
                            <span class="text-xs tabular-nums opacity-70">{{ $count }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <p class="sr-only" aria-live="polite" data-filter-status></p>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4" data-filter-grid data-stagger>
                @foreach ($products as $product)
                    <x-cards.product :product="$product" :example="$productsAreExamples" />
                @endforeach

                <div class="mt-4 flex flex-col gap-4 border-t border-film-600 pt-10 sm:col-span-2 lg:col-span-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <h3 class="text-lg font-semibold text-ink">Looking for something specific?</h3>
                        <p class="mt-1 text-sm leading-relaxed text-ink-2">
                            Tell us what you need, even if you are not sure of the exact product name, and we will help you find suitable options.
                        </p>
                    </div>
                    <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center sm:gap-6">
                        <x-button href="#enquiry" icon="arrow-right">Send an enquiry</x-button>
                        <a href="{{ route('contact') }}" class="group inline-flex min-h-11 items-center self-start text-sm font-semibold text-ink sm:self-auto">
                            <span class="link-draw">Contact Our Team</span>
                        </a>
                    </div>
                </div>
            </div>

            @if ($productsAreExamples)
                <p class="mt-10 text-sm text-ink-3">
                    Cards marked “Example listing” show the type of equipment we can help with. They are not a list of current stock.
                </p>
            @endif
        </div>
    </section>

    <x-home.industries />

    {{-- ============================================================
         Services
         ============================================================ --}}
    <section id="services" class="scroll-mt-24 bg-film-900 py-16 sm:py-24" aria-labelledby="services-heading">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-12 lg:gap-12 lg:px-8">
            <div class="lg:col-span-5">
                <div class="lg:sticky lg:top-28">
                    <div data-reveal>
                        <x-section-heading dark id="services-heading" eyebrow="Services" title="Support for your equipment, from set-up onwards">
                            <x-slot:description>
                                Imaging equipment needs the right set-up and care. Here is how our team can help.
                            </x-slot:description>
                        </x-section-heading>
                    </div>

                    <div class="relative mt-10 pr-10 pb-12 sm:pr-24" data-reveal>
                        <div class="overflow-hidden rounded-2xl bg-film-700">
                            <x-photo name="technician" alt="A technician’s hands checking connections on a piece of medical equipment with a small torch"
                                     sizes="(min-width: 1024px) 460px, 100vw" class="aspect-[4/3]" />
                        </div>
                        <div class="absolute right-0 bottom-0 w-[42%] overflow-hidden rounded-2xl bg-film-800 p-1.5 shadow-card-hover ring-1 ring-film-600">
                            <x-photo name="tube-head" alt="Close-up of an X-ray tube head with its control handles"
                                     sizes="200px" class="aspect-square rounded-xl" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:col-span-7" data-stagger>
                @foreach ($services as $service)
                    <x-cards.service :service="$service" :index="$loop->index" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         Interactive explainer
         ============================================================ --}}
    <section id="xray-room" class="scroll-mt-24 border-t border-film-600 bg-film-900 py-16 sm:py-24" aria-labelledby="room-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div data-reveal>
                <x-section-heading dark id="room-heading" eyebrow="Equipment explained" title="What’s inside a digital X-ray room?">
                    <x-slot:description>
                        New to imaging equipment? Here are the main parts of a typical room. Select a number to see what each part does.
                    </x-slot:description>
                </x-section-heading>
            </div>

            <div class="mt-10" data-reveal>
                <x-room-explorer />
            </div>
        </div>
    </section>

    {{-- ============================================================
         Gallery + projects
         ============================================================ --}}
    <section id="gallery" class="scroll-mt-24 py-16 sm:py-24" aria-labelledby="gallery-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between" data-reveal>
                <x-section-heading dark id="gallery-heading" eyebrow="Gallery" title="Imaging equipment in real settings">
                    <x-slot:description>
                        A look at the kinds of equipment and environments we work with. Select a photo to view it larger.
                    </x-slot:description>
                </x-section-heading>
            </div>

            @if (count($projects))
                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal>
                    @foreach ($projects as $project)
                        <x-cards.project :project="$project" :featured="$loop->first" :class="$loop->first ? 'md:col-span-2' : ''" />
                    @endforeach
                </div>
            @endif

            <x-gallery :items="$gallery" class="mt-10" />

            <div class="mt-10 flex flex-col gap-4 rounded-2xl bg-film-900 p-6 ring-1 ring-film-600 sm:flex-row sm:items-center sm:justify-between" data-reveal>
                <p class="flex gap-3 text-base text-ink-2">
                    <x-icon name="folder" class="mt-0.5 size-5 shrink-0 text-glow-300" />
                    <span>
                        @if (count($projects))
                            <a href="{{ route('portfolio.index') }}" class="font-semibold text-ink underline decoration-film-500 underline-offset-4 hover:decoration-glow-300">See all projects.</a>
                            Gallery photos are representative and are not ZenRay client installations.
                        @else
                            <span class="font-semibold text-ink">Project case studies are coming soon.</span>
                            Photos above are representative and are not ZenRay client installations.
                        @endif
                    </span>
                </p>
                <a href="{{ route('credits') }}" class="shrink-0 text-sm font-semibold text-ink underline decoration-film-500 underline-offset-4 hover:decoration-glow-300">Image credits</a>
            </div>
        </div>
    </section>

    {{-- ============================================================
         Latest from the blog
         ============================================================ --}}
    @if ($posts->isNotEmpty())
        <section id="blog" class="scroll-mt-24 border-t border-film-600 py-16 sm:py-24" aria-labelledby="blog-heading">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between" data-reveal>
                    <x-section-heading dark id="blog-heading" eyebrow="From the blog" title="Guides and insights">
                        <x-slot:description>
                            Plain-language articles to help you choose, use and look after imaging equipment.
                        </x-slot:description>
                    </x-section-heading>
                    <x-button :href="route('blog.index')" variant="outline-dark" icon="arrow-right" class="self-start lg:self-auto">All articles</x-button>
                </div>

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-stagger>
                    @foreach ($posts as $post)
                        <x-cards.post :post="$post" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-home.contact />

    <x-product-quick-view />
</x-layouts.app>
