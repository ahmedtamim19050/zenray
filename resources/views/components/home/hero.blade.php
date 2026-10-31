{{--
    Homepage hero: product showcase carousel styled as a radiograph viewer.
    Each slide's photo is shown as an X-ray negative; a scanning beam (and,
    on fine pointers, a lens that follows the cursor) reveals the real photo.
    Text and media are two synchronised stacks (data-slide-text / data-slide-media);
    app.js switches data-active. The first slide is active without JavaScript.
    Auto-advance is driven by the active tab's progress-bar animation ending.
--}}
@php
    $slides = config('zenray.hero_slides');
    $count = count($slides);
    $pad = fn (int $n) => str_pad($n, 2, '0', STR_PAD_LEFT);
@endphp

<section class="relative isolate overflow-hidden bg-film-950"
         aria-roledescription="carousel" aria-labelledby="hero-heading" data-carousel style="--carousel-duration: 7s">
    {{-- Backdrop: light-box glow + measurement grid --}}
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <div class="bg-grid-dark absolute inset-0 [mask-image:radial-gradient(ellipse_80%_70%_at_70%_40%,black,transparent)]"></div>
        <div class="hero-aurora absolute -top-40 right-[-10%] size-[46rem] rounded-full bg-glow-500/20 blur-[140px]"></div>
        <div class="hero-aurora is-alt absolute -bottom-56 left-[-12%] size-[36rem] rounded-full bg-signal-500/10 blur-[140px]"></div>
    </div>

    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 pt-10 pb-16 sm:px-6 sm:pt-14 lg:min-h-[44rem] lg:grid-cols-12 lg:gap-8 lg:px-8 lg:pt-12 lg:pb-20">
        {{-- Copy --}}
        <div class="order-2 lg:order-1 lg:col-span-5">
            <h1 id="hero-heading" class="hero-in inline-flex items-center gap-3 font-mono text-xs font-semibold tracking-[0.2em] text-glow-300 uppercase sm:text-sm" style="--d: .05s">
                <span class="h-px w-10 bg-glow-400 shadow-[0_0_8px_rgb(46_199_240)]" aria-hidden="true"></span>
                Medical imaging equipment &amp; support
            </h1>

            <div class="mt-6 grid">
                @foreach ($slides as $i => $slide)
                    <div class="carousel-slide carousel-text col-start-1 row-start-1" data-slide-text
                         role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} of {{ $count }}: {{ $slide['title'] }}"
                         @if ($i === 0) data-active @else inert @endif>
                        <p class="inline-flex items-center gap-2 rounded-md bg-glow-400/10 px-3 py-1 font-mono text-xs font-semibold tracking-wider text-glow-100 uppercase ring-1 ring-glow-400/30">
                            <span class="text-signal-400">{{ $pad($i + 1) }}</span>
                            <span class="h-3 w-px bg-glow-400/40" aria-hidden="true"></span>
                            {{ $slide['eyebrow'] }}
                        </p>
                        <h2 class="text-glow mt-5 text-[2.75rem] leading-[1] font-bold tracking-[-0.045em] text-balance text-ink sm:text-6xl xl:text-7xl">
                            {{ $slide['title'] }}
                        </h2>
                        <p class="mt-6 max-w-md text-lg leading-relaxed text-pretty text-ink-2">{{ $slide['text'] }}</p>
                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <x-button href="#featured-equipment" data-filter-link="{{ $slide['category'] }}" variant="glow" size="lg" icon="arrow-right" class="btn-shine">
                                Explore {{ Str::of($slide['eyebrow'])->lower() }}
                            </x-button>
                            <x-button :href="route('quote', ['interest' => $slide['category']])" size="lg" class="btn-shine">Request a Quote</x-button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Controls --}}
            <div class="mt-12 flex items-end gap-4">
                <ol class="grid flex-1 grid-cols-4 gap-3" aria-label="Choose a slide">
                    @foreach ($slides as $i => $slide)
                        <li>
                            <button type="button" data-carousel-tab="{{ $i }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                                    aria-label="Show slide {{ $i + 1 }}: {{ $slide['title'] }}"
                                    class="group w-full pt-3 text-left">
                                <span class="block h-1 overflow-hidden rounded-full bg-film-600">
                                    <span class="carousel-progress block h-full rounded-full bg-glow-400 shadow-[0_0_10px_rgb(46_199_240)]"></span>
                                </span>
                                <span class="mt-2.5 block font-mono text-xs text-ink-3 transition-colors group-hover:text-glow-200 group-aria-[current=true]:text-glow-200">{{ $pad($i + 1) }}</span>
                                <span class="mt-0.5 hidden truncate text-xs font-semibold text-ink-3 transition-colors group-hover:text-ink group-aria-[current=true]:text-ink sm:block">{{ $slide['title'] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ol>
                <div class="flex shrink-0 gap-1.5">
                    @php($control = 'flex size-10 items-center justify-center rounded-full bg-film-800 text-glow-100 ring-1 ring-glow-400/25 transition hover:bg-glow-400 hover:text-film-950')
                    <button type="button" data-carousel-prev class="{{ $control }}">
                        <x-icon name="chevron-left" class="size-4" /><span class="sr-only">Previous slide</span>
                    </button>
                    <button type="button" data-carousel-next class="{{ $control }}">
                        <x-icon name="chevron-right" class="size-4" /><span class="sr-only">Next slide</span>
                    </button>
                    <button type="button" data-carousel-toggle class="{{ $control }}">
                        <svg class="carousel-icon-pause size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z" /></svg>
                        <svg class="carousel-icon-play hidden size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l10.5-6.5z" /></svg>
                        <span class="sr-only" data-carousel-toggle-label>Pause slideshow</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Viewer --}}
        <div class="order-1 lg:order-2 lg:col-span-7">
            <div data-tilt="3" class="relative mx-auto w-full max-w-2xl rounded-[1.75rem] bg-film-900 p-2 shadow-[0_50px_100px_-30px_rgb(0_0_0/0.9),0_0_80px_-30px_rgb(46_199_240/0.5)] ring-1 ring-glow-400/20 sm:p-3 lg:max-w-none">
                {{-- Viewer title bar --}}
                <div class="flex items-center justify-between gap-3 px-2 pt-1 pb-3 font-mono text-[0.6875rem] tracking-widest text-ink-3 uppercase sm:px-3">
                    <span class="flex items-center gap-2">
                        <span class="hud-blink size-2 rounded-full bg-signal-500 shadow-[0_0_8px_rgb(242_140_40)]" aria-hidden="true"></span>
                        <span class="text-glow-200">ZenRay</span> DR&nbsp;Viewer
                    </span>
                    <span class="hidden sm:inline">Radiograph mode</span>
                </div>

                <div class="relative aspect-[5/4] overflow-hidden rounded-[1.25rem] bg-film-950 ring-1 ring-glow-400/15">
                    @foreach ($slides as $i => $slide)
                        <div class="carousel-slide carousel-media absolute inset-0" data-slide-media @if ($i === 0) data-active @else inert @endif>
                            <x-xray :name="$slide['photo']" :alt="$slide['alt']" base="xray" scan
                                    :priority="$i === 0" :loading="$i === 0 ? null : 'eager'"
                                    sizes="(min-width: 1280px) 700px, (min-width: 1024px) 58vw, 100vw"
                                    img-class="carousel-kenburns object-center" class="absolute inset-0">
                                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_55%,rgb(2_7_11/0.75))]" aria-hidden="true"></div>

                                {{-- Side marker, like the lead "R" placed on a radiograph --}}
                                <span class="absolute top-4 left-4 flex size-8 items-center justify-center rounded border border-glow-200/70 font-mono text-sm font-bold text-glow-100 [text-shadow:0_0_8px_rgb(46_199_240)]" aria-hidden="true">R</span>
                                <span class="absolute top-4 right-4 font-mono text-[0.6875rem] tracking-widest text-glow-200/80" aria-hidden="true">IMG {{ $pad($i + 1) }}/{{ $pad($count) }}</span>

                                <span class="absolute bottom-4 left-4 inline-flex items-center gap-2 rounded-md bg-film-950/70 px-3 py-1.5 font-mono text-[0.6875rem] font-semibold tracking-wider text-glow-100 uppercase ring-1 ring-glow-400/30 backdrop-blur-md">
                                    <span class="size-1.5 rounded-full bg-signal-500" aria-hidden="true"></span>
                                    {{ $slide['eyebrow'] }}
                                </span>
                            </x-xray>
                        </div>
                    @endforeach

                    <div class="hud-corners pointer-events-none absolute inset-3" aria-hidden="true"></div>
                    <div class="hud-ruler pointer-events-none absolute inset-x-12 bottom-0 h-2.5 opacity-70" aria-hidden="true"></div>
                </div>

                <p class="flex items-center justify-between gap-3 px-2 pt-3 pb-1 font-mono text-[0.6875rem] tracking-widest text-ink-3 uppercase sm:px-3" aria-hidden="true">
                    <span class="hidden [@media(hover:hover)_and_(pointer:fine)]:inline">Move your cursor over the image to see the real photo</span>
                    <span class="[@media(hover:hover)_and_(pointer:fine)]:hidden">Scanning…</span>
                    <span class="text-glow-300">● Live</span>
                </p>
            </div>

            <a href="{{ route('contact') }}"
               class="float-slow group relative z-10 mx-auto -mt-6 hidden w-fit items-center gap-3 rounded-2xl bg-film-800/90 p-3 pr-5 shadow-[0_24px_60px_-20px_rgb(0_0_0/0.9)] ring-1 ring-glow-400/25 backdrop-blur-xl transition hover:ring-signal-400 sm:flex lg:mr-auto lg:ml-[-1.5rem]">
                <span class="flex size-10 items-center justify-center rounded-xl bg-glow-400/15 text-glow-200 ring-1 ring-glow-400/30 transition-colors group-hover:bg-signal-500 group-hover:text-film-950">
                    <x-icon name="chat" class="size-5" />
                </span>
                <span class="text-sm leading-tight">
                    <span class="block font-semibold text-ink">Ask for models &amp; pricing</span>
                    <span class="block text-ink-3">Tell us what your facility needs</span>
                </span>
            </a>
        </div>
    </div>
</section>
