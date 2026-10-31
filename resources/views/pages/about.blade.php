@php
    $values = [
        ['icon' => 'document', 'title' => 'Clarity', 'text' => 'We explain equipment and options in plain language, so decisions are easier.'],
        ['icon' => 'chat', 'title' => 'Communication', 'text' => 'You can speak with our team directly, and we keep you informed along the way.'],
        ['icon' => 'support', 'title' => 'Support', 'text' => 'We aim to help beyond the sale, with installation, maintenance and advice.'],
        ['icon' => 'shield', 'title' => 'Care', 'text' => 'We treat every request seriously, because imaging equipment supports patient care.'],
    ];
@endphp

<x-layouts.app title="About Us" description="About ZenRay Medical Systems: medical imaging equipment, installation and technical support for healthcare facilities.">
    <x-page-hero eyebrow="About Us" title="Making medical imaging equipment easier to navigate"
                 :description="config('zenray.name').' helps healthcare facilities find suitable imaging equipment, and supports them with installation, maintenance and technical advice.'"
                 photo="room-overview" photo-alt="Digital X-ray room with patient table, tube stand and wall stand">
        <x-button :href="route('contact')" size="lg" icon="arrow-right" class="btn-shine">Talk to Our Team</x-button>
    </x-page-hero>

    <section class="py-20 sm:py-28" aria-labelledby="story-heading">
        <div class="mx-auto grid max-w-7xl items-center gap-14 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <x-section-heading dark id="story-heading" eyebrow="What we do" title="Equipment and support from one team" />
                <div class="mt-6 space-y-4 text-lg leading-relaxed text-ink-2" data-reveal>
                    <p>Choosing imaging equipment can be complicated. There are many systems, accessories and service options, and the right choice depends on your facility, your space and the care you provide.</p>
                    <p>Our role is to make that process simpler. We listen to your requirements, explain the options clearly, and support you from the first conversation through installation and ongoing maintenance.</p>
                </div>
                <div class="mt-8 flex flex-wrap gap-3" data-reveal>
                    <x-button :href="route('products.index')" variant="glow" icon="arrow-right">Explore equipment</x-button>
                    <x-button :href="route('services.index')" variant="outline-dark">Our services</x-button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4" data-stagger>
                <div class="row-span-2 overflow-hidden rounded-[2rem] bg-film-700"><x-photo name="c-arm" alt="Mobile C-arm in an operating theatre" sizes="(min-width: 1024px) 300px, 50vw" class="h-full min-h-80" /></div>
                <div class="overflow-hidden rounded-[2rem] bg-film-700"><x-photo name="detector-panel" alt="Flat-panel digital X-ray detector" sizes="(min-width: 1024px) 300px, 50vw" class="aspect-square" /></div>
                <div class="overflow-hidden rounded-[2rem] bg-film-700"><x-photo name="tube-head" alt="X-ray tube head and controls" sizes="(min-width: 1024px) 300px, 50vw" class="aspect-square" /></div>
            </div>
        </div>
    </section>

    <section class="bg-film-900 py-20 sm:py-28" aria-labelledby="values-heading">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-heading dark id="values-heading" eyebrow="How we work" title="What you can expect from us" align="center" />
            <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4" data-stagger>
                @foreach ($values as $value)
                    <div data-spotlight class="group rounded-3xl bg-film-800 p-7 ring-1 ring-film-600 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover">
                        <span class="flex size-12 items-center justify-center rounded-2xl bg-linear-to-br from-signal-400 to-signal-600 text-navy-950 transition-transform duration-500 group-hover:scale-110 group-hover:-rotate-6"><x-icon :name="$value['icon']" class="size-6" /></span>
                        <h3 class="mt-6 text-lg font-bold text-ink">{{ $value['title'] }}</h3>
                        <p class="mt-2 text-[0.9375rem] leading-relaxed text-ink-2">{{ $value['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <x-home.industries />

    <section class="py-20 sm:py-28">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h2 data-split class="text-3xl font-bold tracking-tight text-ink sm:text-5xl">Let’s talk about your facility</h2>
            <p class="mx-auto mt-5 max-w-xl text-lg text-ink-2">Tell us what you need and our team will get in touch to discuss the options.</p>
            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <x-button :href="route('quote')" size="lg" icon="arrow-right" class="btn-shine">Request a Quote</x-button>
                <x-button :href="route('contact')" variant="outline-dark" size="lg">Contact Our Team</x-button>
            </div>
        </div>
    </section>
</x-layouts.app>
