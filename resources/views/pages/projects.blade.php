<x-layouts.app title="Projects" description="Projects and imaging equipment gallery from ZenRay Medical Systems.">
    <x-page-hero eyebrow="Projects" title="Our projects and gallery"
                 :description="count($projects) ? 'Installations and service work we have delivered, published with the permission of the facilities involved.' : 'Case studies of installations and service work will be published here once they are approved by the facilities involved.'" />

    <section class="py-16 sm:py-24" aria-labelledby="case-studies-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (count($projects))
                <h2 id="case-studies-heading" class="sr-only">Case studies</h2>
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3" data-stagger>
                    @foreach ($projects as $project)
                        <x-cards.project :project="$project" :featured="$loop->first" :class="$loop->first ? 'md:col-span-2' : ''" />
                    @endforeach
                </div>
            @else
            <div class="grid overflow-hidden rounded-2xl bg-film-900 ring-1 ring-film-600 lg:grid-cols-2" data-reveal>
                <div class="flex flex-col justify-center p-8 sm:p-8">
                    <span class="flex size-10 items-center justify-center rounded-2xl bg-glow-400/10 text-glow-300 ring-1 ring-glow-400/25"><x-icon name="folder" class="size-6" /></span>
                    <h2 id="case-studies-heading" class="mt-5 text-2xl font-bold tracking-tight text-ink">Case studies coming soon</h2>
                    <p class="mt-3 max-w-md leading-relaxed text-ink-2">We only publish projects with the permission of the facilities involved. If you would like to hear about the kind of work we do, our team is happy to talk.</p>
                    <div class="mt-10 flex flex-wrap gap-3">
                        <x-button :href="route('contact')" icon="arrow-right">Contact Our Team</x-button>
                        <x-button :href="route('services.index')" variant="outline-dark">Our services</x-button>
                    </div>
                </div>
                <div class="relative min-h-72 overflow-hidden">
                    <x-photo name="room-overview" alt="General digital X-ray room" sizes="(min-width: 1024px) 50vw, 100vw" class="absolute inset-0" />
                    <div class="absolute inset-0 bg-linear-to-r from-film-900 via-film-900/30 to-transparent lg:from-film-900/90" aria-hidden="true"></div>
                </div>
            </div>
            @endif
        </div>
    </section>

    <section class="pb-16 sm:pb-24" aria-labelledby="gallery-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading dark id="gallery-heading" eyebrow="Gallery" title="Imaging equipment in real settings">
                <x-slot:description>Representative photos of the kinds of equipment we work with. They are not ZenRay client installations. Select a photo to view it larger.</x-slot:description>
            </x-section-heading>
            <x-gallery :items="$gallery" class="mt-10" />
            <p class="mt-6 text-sm text-ink-3"><a href="{{ route('credits') }}" class="underline underline-offset-4">Image credits</a></p>
        </div>
    </section>
</x-layouts.app>
