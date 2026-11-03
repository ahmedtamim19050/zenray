{{--
    Homepage hero: a short product showcase. Text and photos are two synchronised
    stacks (data-slide-text / data-slide-media) that app.js switches and motion.css
    animates. Auto-advance is driven by the active tab's loading line finishing, and
    a scan beam crosses the photo in the same time, turning it into an X-ray negative.
    Hovering or keyboard focus pauses both; choosing a tab stops them and shows the
    plain photo. The first slide is active without JavaScript.
--}}
@php
    $slides = config('zenray.hero_slides');
    $count = count($slides);
@endphp

<section class="bg-film-950" aria-roledescription="carousel" aria-labelledby="hero-heading" data-carousel style="--carousel-duration: 8s">
    <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-12 sm:px-6 lg:grid-cols-12 lg:gap-12 lg:px-8 lg:py-20">
        {{-- Copy --}}
        <div class="lg:col-span-6">
            <h1 id="hero-heading" class="hero-in text-sm font-semibold text-signal-text">
                Medical imaging equipment &amp; support
            </h1>

            <div class="mt-5 grid">
                @foreach ($slides as $i => $slide)
                    <div class="carousel-slide carousel-text col-start-1 row-start-1" data-slide-text
                         role="group" aria-roledescription="slide" aria-label="{{ $i + 1 }} of {{ $count }}: {{ $slide['title'] }}"
                         @if ($i === 0) data-active @else inert @endif>
                        <h2 class="text-3xl leading-tight font-semibold tracking-tight text-balance text-ink sm:text-4xl">
                            {{ $slide['title'] }}
                        </h2>
                        <p class="mt-6 max-w-lg text-lg leading-relaxed text-pretty text-ink-2">{{ $slide['text'] }}</p>
                        <div class="mt-8">
                            <x-button :href="route('quote', ['interest' => $slide['category']])" size="lg" icon="arrow-right">Request a Quote</x-button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Tabs: the orange line on the active tab fills like a loading bar (motion.css) --}}
            <div class="hero-in mt-10 flex items-start gap-3">
                <ol class="grid flex-1 grid-cols-2 gap-x-4 sm:auto-cols-fr sm:grid-flow-col" aria-label="Choose a slide">
                    @foreach ($slides as $i => $slide)
                        <li>
                            <button type="button" data-carousel-tab="{{ $i }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                                    class="hero-tab relative flex size-full items-start border-t border-film-600 py-3 text-left text-sm leading-snug font-medium text-ink-3 transition-colors duration-300 hover:text-ink aria-[current=true]:text-ink">
                                {{ $slide['title'] }}
                            </button>
                        </li>
                    @endforeach
                </ol>

                @if ($count > 1)
                    <button type="button" data-carousel-toggle hidden title="Pause slideshow"
                            class="flex size-11 shrink-0 items-center justify-center rounded-full text-ink-2 ring-1 ring-film-600 transition-colors hover:bg-film-700 hover:text-ink">
                        <svg class="carousel-icon-pause size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z" /></svg>
                        <svg class="carousel-icon-play size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l10.5-6.5z" /></svg>
                        <span class="sr-only" data-carousel-toggle-label>Pause slideshow</span>
                    </button>
                @endif
            </div>
        </div>

        {{-- Photo --}}
        <div class="hero-in lg:col-span-6">
            <div class="relative aspect-[3/2] overflow-hidden rounded-2xl bg-film-700">
                @foreach ($slides as $i => $slide)
                    <div class="carousel-slide carousel-media absolute inset-0" data-slide-media @if ($i === 0) data-active @else inert @endif>
                        <x-photo :name="$slide['photo']" :alt="$slide['alt']"
                                 :priority="$i === 0" :loading="$i === 0 ? null : 'eager'"
                                 sizes="(min-width: 1280px) 620px, (min-width: 1024px) 50vw, 100vw" />
                        {{-- X-ray negative revealed behind the scan beam while the slideshow plays --}}
                        <x-photo :name="$slide['photo']" alt="" aria-hidden="true" loading="eager"
                                 sizes="(min-width: 1280px) 620px, (min-width: 1024px) 50vw, 100vw"
                                 class="hero-scan-negative absolute inset-0" />
                        <span class="hero-scan-beam" aria-hidden="true"></span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
