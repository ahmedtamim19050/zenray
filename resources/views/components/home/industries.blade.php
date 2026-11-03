{{--
    "Who we serve": facility types (not named clients) as a list beside one large photo.
    Hovering or focusing a row switches the photo (app.js, [data-industries]). Small screens
    show a thumbnail in each row instead. Photos are representative and repeat in order, so
    facility types added from the admin always get one.
--}}
@php
    $industries = config('zenray.industries');
    $photos = ['angio-suite', 'ultrasound', 'dr-wallstand', 'hero-room', 'c-arm', 'xray-mobile'];
    $contactUrl = route('contact', ['interest' => 'other']);
@endphp

<section id="industries" class="scroll-mt-24 bg-film-900 py-16 sm:py-24" aria-labelledby="industries-heading" data-industries>
    <div class="mx-auto grid max-w-7xl items-start gap-10 px-4 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8">
        <div class="lg:sticky lg:top-24 lg:col-span-5">
            <x-section-heading id="industries-heading" eyebrow="Who we serve" title="Solutions for every kind of healthcare facility" dark>
                <x-slot:description>
                    From large hospitals to small practices, we help match equipment and support to the way your facility works.
                </x-slot:description>
            </x-section-heading>

            <a href="{{ $contactUrl }}" class="group mt-5 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-ink">
                <span class="link-draw">Discuss your facility</span>
                <x-icon name="arrow-right" class="size-4 transition-transform duration-300 ease-calm group-hover:translate-x-1" />
            </a>

            <div class="relative mt-8 hidden aspect-[4/3] overflow-hidden rounded-2xl bg-film-700 lg:block" data-reveal>
                @foreach ($industries as $industry)
                    <div class="carousel-slide carousel-media absolute inset-0" data-industry-photo @if ($loop->first) data-active @endif>
                        <x-photo :name="$photos[$loop->index % count($photos)]" alt="" sizes="480px" />
                    </div>
                @endforeach
            </div>
        </div>

        <ul class="border-t border-film-600 lg:col-span-7" data-stagger>
            @foreach ($industries as $industry)
                <li class="group relative border-b border-film-600" data-industry-row @if ($loop->first) data-active @endif>
                    <a href="{{ $contactUrl }}" class="flex items-center gap-5 py-6 -outline-offset-2">
                        <span class="size-16 shrink-0 overflow-hidden rounded-xl bg-film-700 lg:hidden">
                            <x-photo :name="$photos[$loop->index % count($photos)]" alt="" sizes="64px" />
                        </span>
                        <span class="hidden size-11 shrink-0 items-center justify-center rounded-xl bg-glow-400/10 text-glow-300 ring-1 ring-glow-400/25 transition-colors duration-300 lg:flex lg:group-data-active:bg-signal-500 lg:group-data-active:text-navy-950 lg:group-data-active:ring-signal-500">
                            <x-icon :name="$industry['icon']" class="size-5" />
                        </span>

                        <span class="min-w-0 flex-1 transition-transform duration-300 ease-calm lg:motion-safe:group-data-active:translate-x-1">
                            <span class="block text-lg leading-snug font-semibold tracking-tight text-ink sm:text-xl">{{ $industry['name'] }}</span>
                            <span class="mt-1 block text-sm leading-relaxed text-ink-2">{{ $industry['text'] }}</span>
                        </span>

                        <span class="hidden size-10 shrink-0 items-center justify-center rounded-full text-ink ring-1 ring-film-500 transition-colors duration-300 sm:flex lg:group-data-active:bg-signal-500 lg:group-data-active:text-navy-950 lg:group-data-active:ring-signal-500">
                            <x-icon name="arrow-right" class="size-4 -rotate-45 transition-transform duration-300 ease-calm lg:group-data-active:rotate-0" />
                        </span>
                        <span class="sr-only">Talk to us about {{ Str::lower($industry['name']) }}</span>
                    </a>

                    {{-- Orange line that draws along the bottom of the active row --}}
                    <span class="absolute inset-x-0 -bottom-px h-0.5 origin-left scale-x-0 bg-signal-500 transition-transform duration-500 ease-calm lg:group-data-active:scale-x-100" aria-hidden="true"></span>
                </li>
            @endforeach
        </ul>
    </div>
</section>
