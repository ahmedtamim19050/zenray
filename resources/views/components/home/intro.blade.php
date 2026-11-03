{{-- What we offer tiles + company intro + optional verified stats band --}}
@php
    $capabilities = [
        ['icon' => 'xray', 'title' => 'Imaging equipment', 'text' => 'X-ray, digital radiography and diagnostic systems', 'href' => route('products.index'), 'tile' => 'photo', 'grid' => 'sm:col-span-2 lg:col-span-6 lg:row-span-2'],
        ['icon' => 'install', 'title' => 'Installation', 'text' => 'Set-up and preparation for everyday use', 'href' => route('services.index').'#installation', 'tile' => 'surface', 'grid' => 'lg:col-span-3'],
        ['icon' => 'support', 'title' => 'Maintenance & support', 'text' => 'Help keeping equipment in working order', 'href' => route('services.index').'#maintenance', 'tile' => 'surface', 'grid' => 'lg:col-span-3'],
        ['icon' => 'consult', 'title' => 'Consultation', 'text' => 'Guidance on choosing the right options', 'href' => route('services.index').'#consultation', 'tile' => 'navy', 'grid' => 'sm:col-span-2 lg:col-span-6'],
    ];
    $stats = config('zenray.stats', []);
@endphp

{{-- What we offer --}}
<section aria-labelledby="offer-heading">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <x-section-heading dark id="offer-heading" eyebrow="What we offer" title="Equipment and support from one team" />

        <ul class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-12" data-stagger>
            @foreach ($capabilities as $capability)
                @php($tile = $capability['tile'])
                <li class="{{ $capability['grid'] }}">
                    <a href="{{ $capability['href'] }}"
                       @class([
                           'group relative isolate flex size-full flex-col overflow-hidden rounded-2xl p-7 transition duration-300 ease-calm hover:shadow-card-hover motion-safe:hover:-translate-y-1 sm:p-7',
                           'min-h-72 text-white lg:min-h-[24rem]' => $tile === 'photo',
                           'bg-film-900 text-ink ring-1 ring-film-600' => $tile === 'surface',
                           'bg-navy-800 text-white focus-visible:outline-signal-400' => $tile === 'navy',
                       ])>
                        @if ($tile === 'photo')
                            <x-photo name="room-overview" alt="" sizes="(min-width: 1024px) 600px, 100vw"
                                     class="media-settle absolute inset-0 -z-20 transition-[scale] duration-700 ease-calm motion-safe:group-hover:scale-105" />
                            <span class="absolute inset-0 -z-10 bg-linear-to-t from-black/85 via-black/35 to-black/10" aria-hidden="true"></span>
                        @endif

                        <span class="flex items-start justify-between gap-4">
                            <span @class([
                                'flex size-10 items-center justify-center rounded-xl',
                                'bg-glow-400/10 text-glow-300 ring-1 ring-glow-400/25' => $tile === 'surface',
                                'bg-white/15 text-white ring-1 ring-white/25 backdrop-blur-md' => $tile !== 'surface',
                            ])>
                                <x-icon :name="$capability['icon']" class="size-6" />
                            </span>
                            {{-- The arrow turns to point forward and fills orange on hover --}}
                            <span @class([
                                'flex size-9 items-center justify-center rounded-full ring-1 transition-colors duration-300 group-hover:bg-signal-500 group-hover:text-navy-950 group-hover:ring-signal-500 group-focus-visible:bg-signal-500 group-focus-visible:text-navy-950',
                                'ring-film-500' => $tile === 'surface',
                                'ring-white/40' => $tile !== 'surface',
                            ])>
                                <x-icon name="arrow-right" class="size-4 -rotate-45 transition-transform duration-300 ease-calm group-hover:rotate-0 group-focus-visible:rotate-0" />
                            </span>
                        </span>

                        <span class="mt-auto block pt-12">
                            <span @class(['block font-semibold tracking-tight', 'text-xl sm:text-2xl' => $tile === 'photo', 'text-lg' => $tile !== 'photo'])>{{ $capability['title'] }}</span>
                            <span @class([
                                'mt-2 block text-sm leading-relaxed',
                                'text-ink-2' => $tile === 'surface',
                                'text-white/90' => $tile === 'photo',
                                'text-navy-100' => $tile === 'navy',
                            ])>{{ $capability['text'] }}</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- Intro --}}
<section id="about" class="scroll-mt-24 pt-20 pb-16 sm:pt-28 sm:pb-24" aria-labelledby="about-heading">
    <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 sm:px-6 lg:grid-cols-12 lg:gap-12 lg:px-8">
        <div class="lg:col-span-6">
            <p class="text-sm font-semibold text-signal-text">
                About {{ config('zenray.short_name') }}
            </p>
            <h2 id="about-heading" data-reveal class="mt-3 text-2xl leading-tight font-semibold tracking-tight text-balance text-ink sm:text-3xl">
                Imaging equipment, and the support to keep it running.
            </h2>
            <p class="mt-6 max-w-xl text-lg leading-relaxed text-ink-2" data-reveal>
                {{ config('zenray.name') }} helps healthcare facilities find suitable medical imaging equipment, then supports them with installation, maintenance and technical advice.
                We explain options in plain language, so you can make confident decisions.
            </p>

            <ul class="mt-10 grid gap-6 sm:grid-cols-2" data-stagger>
                @foreach (['Plain-language equipment guidance', 'One team for equipment and service', 'Straightforward quotations', 'Direct contact with our team'] as $point)
                    <li class="flex items-center gap-3 rounded-2xl bg-film-900 px-4 py-3 text-base font-medium text-ink ring-1 ring-film-600">
                        <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-signal-500 text-navy-950">
                            <x-icon name="check" class="size-3.5" />
                        </span>
                        {{ $point }}
                    </li>
                @endforeach
            </ul>

            <div class="mt-10 flex flex-wrap items-center gap-4" data-reveal>
                <x-button :href="route('about')" variant="outline-dark" icon="arrow-right">More about us</x-button>
                <a href="#services" class="text-sm font-semibold text-ink underline decoration-film-500 underline-offset-4 hover:decoration-glow-300">See our services</a>
            </div>
        </div>

        {{-- Image collage --}}
        <div class="relative lg:col-span-6" data-reveal>
            <div class="grid grid-cols-5 grid-rows-6 gap-4 sm:h-[30rem]">
                <div class="col-span-3 row-span-6 overflow-hidden rounded-2xl bg-film-700">
                    <x-photo name="angio-suite" alt="Ceiling-mounted X-ray system in a procedure room" sizes="(min-width: 1024px) 380px, 60vw" class="h-full min-h-72" />
                </div>
                <div class="group col-span-2 row-span-3 overflow-hidden rounded-2xl bg-film-700">
                    <x-photo name="dr-wallstand" alt="Wall-mounted digital X-ray detector on a vertical stand" sizes="(min-width: 1024px) 250px, 40vw"  class="" />
                </div>
                <div class="group col-span-2 row-span-3 overflow-hidden rounded-2xl bg-film-700">
                    <x-photo name="xray-mobile" alt="Mobile X-ray unit with its tube arm folded for moving" sizes="(min-width: 1024px) 250px, 40vw"  class="" />
                </div>
            </div>
            <div class="absolute -bottom-6 left-6 flex items-center gap-3 rounded-2xl bg-film-800 p-4 pr-6 shadow-card-hover ring-1 ring-film-600 sm:left-10">
                <img src="{{ asset('images/zenray-mark.svg') }}" alt="" width="160" height="112" class="h-9 w-auto dark:hidden">
                <img src="{{ asset('images/zenray-mark-light.svg') }}" alt="" width="160" height="112" class="hidden h-9 w-auto dark:block">
                <span class="text-sm leading-tight">
                    <span class="block font-bold text-ink">Equipment · Installation · Support</span>
                    <span class="block text-ink-3">One point of contact</span>
                </span>
            </div>
        </div>
    </div>
</section>

{{-- Verified figures only: hidden until config('zenray.stats') is filled --}}
@if ($stats)
    <section class="bg-film-900 py-14 text-ink" aria-label="ZenRay in numbers">
        <dl class="mx-auto grid max-w-7xl gap-10 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8" data-stagger>
            @foreach ($stats as $stat)
                <div class="flex flex-col-reverse border-l-2 border-signal-500 pl-6">
                    <dt class="text-sm font-medium text-ink-2">{{ $stat['label'] }}</dt>
                    <dd class="text-4xl font-bold tracking-tight">{{ $stat['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
@endif
