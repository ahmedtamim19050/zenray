{{-- "Who we serve": facility types (not named clients) --}}
<section id="industries" class="relative isolate scroll-mt-24 overflow-hidden bg-film-950 py-24 text-white sm:py-32" aria-labelledby="industries-heading">
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <div class="hero-aurora absolute -top-40 -left-32 size-[36rem] rounded-full bg-signal-500/20 blur-[120px]"></div>
        <div class="hero-aurora is-alt absolute -right-40 -bottom-40 size-[34rem] rounded-full bg-sky-500/15 blur-[120px]"></div>
        <div class="bg-grid absolute inset-0 [mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)]"></div>
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
            <x-section-heading id="industries-heading" eyebrow="Who we serve" title="Solutions for every kind of healthcare facility" dark>
                <x-slot:description>
                    From large hospitals to small practices, we help match equipment and support to the way your facility works.
                </x-slot:description>
            </x-section-heading>
            <x-button :href="route('contact', ['interest' => 'other'])" variant="outline-light" icon="arrow-right" class="self-start bg-white/5 lg:self-auto">Discuss your facility</x-button>
        </div>

        <ul class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-stagger>
            @foreach (config('zenray.industries') as $industry)
                <li data-spotlight style="--spot: rgb(242 140 40 / 0.22)"
                    class="group relative overflow-hidden rounded-3xl bg-white/[0.04] p-7 ring-1 ring-white/10 transition duration-500 hover:-translate-y-1 hover:bg-white/[0.07] hover:ring-signal-500/50 sm:p-8">
                    <div class="flex items-start justify-between">
                        <span class="flex size-14 items-center justify-center rounded-2xl bg-linear-to-br from-signal-400 to-signal-600 text-navy-950 shadow-[0_10px_30px_-10px_rgb(242_140_40/0.8)] transition-transform duration-500 group-hover:scale-110 group-hover:rotate-[-6deg]">
                            <x-icon :name="$industry['icon']" class="size-7" />
                        </span>
                        <span class="font-mono text-sm text-navy-300" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h3 class="mt-8 text-xl font-bold">{{ $industry['name'] }}</h3>
                    <p class="mt-2 text-[0.9375rem] leading-relaxed text-navy-200">{{ $industry['text'] }}</p>
                    <a href="{{ route('contact', ['interest' => 'other']) }}" class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-signal-400 after:absolute after:inset-0 hover:text-signal-300">
                        Talk to us about {{ Str::lower($industry['name']) }}
                        <x-icon name="arrow-right" class="size-4 transition-transform group-hover:translate-x-1" />
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
