{{-- Capabilities strip + company intro + optional verified stats band --}}
@php
    $capabilities = [
        ['icon' => 'xray', 'title' => 'Imaging equipment', 'text' => 'X-ray, digital radiography and diagnostic systems'],
        ['icon' => 'install', 'title' => 'Installation', 'text' => 'Set-up and preparation for everyday use'],
        ['icon' => 'support', 'title' => 'Maintenance & support', 'text' => 'Help keeping equipment in working order'],
        ['icon' => 'consult', 'title' => 'Consultation', 'text' => 'Guidance on choosing the right options'],
    ];
    $stats = config('zenray.stats', []);
@endphp

{{-- Capabilities --}}
<section class="relative z-10 -mt-4 px-4 sm:px-6 lg:px-8" aria-label="What we offer">
    <ul class="mx-auto grid max-w-7xl overflow-hidden rounded-3xl bg-film-900 text-white shadow-[0_40px_80px_-30px_rgb(5_23_38/0.6)] sm:grid-cols-2 lg:grid-cols-4" data-stagger>
        @foreach ($capabilities as $capability)
            <li data-spotlight style="--spot: rgb(242 140 40 / 0.2)"
                class="group flex items-start gap-4 border-white/10 p-6 transition-colors hover:bg-navy-800 sm:p-7 [&:not(:last-child)]:border-b sm:[&:nth-child(odd)]:border-r lg:border-b-0 lg:[&:not(:last-child)]:border-r">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-signal-400 ring-1 ring-white/10 transition duration-300 group-hover:scale-110 group-hover:bg-signal-500 group-hover:text-navy-950">
                    <x-icon :name="$capability['icon']" class="size-6" />
                </span>
                <span>
                    <span class="block font-bold">{{ $capability['title'] }}</span>
                    <span class="mt-1 block text-sm leading-snug text-navy-200">{{ $capability['text'] }}</span>
                </span>
            </li>
        @endforeach
    </ul>
</section>

{{-- Intro --}}
<section id="about" class="scroll-mt-24 py-24 sm:py-32" aria-labelledby="about-heading">
    <div class="mx-auto grid max-w-7xl items-center gap-16 px-4 sm:px-6 lg:grid-cols-12 lg:gap-12 lg:px-8">
        <div class="lg:col-span-6">
            <p class="inline-flex items-center gap-2 text-xs font-semibold tracking-[0.18em] text-signal-400 uppercase">
                <span class="h-px w-6 bg-current" aria-hidden="true"></span>
                About {{ config('zenray.short_name') }}
            </p>
            <h2 id="about-heading" data-split class="mt-4 text-[2.25rem] leading-[1.05] font-bold tracking-[-0.035em] text-balance text-ink sm:text-5xl xl:text-6xl">
                Imaging equipment, and the support to keep it running.
            </h2>
            <p class="mt-6 max-w-xl text-lg leading-relaxed text-ink-2" data-reveal>
                {{ config('zenray.name') }} helps healthcare facilities find suitable medical imaging equipment, then supports them with installation, maintenance and technical advice.
                We explain options in plain language, so you can make confident decisions.
            </p>

            <ul class="mt-8 grid gap-3 sm:grid-cols-2" data-stagger>
                @foreach (['Plain-language equipment guidance', 'One team for equipment and service', 'Straightforward quotations', 'Direct contact with our team'] as $point)
                    <li class="flex items-center gap-3 rounded-2xl bg-film-900 px-4 py-3 text-[0.9375rem] font-medium text-ink ring-1 ring-film-600">
                        <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-signal-500 text-navy-950">
                            <x-icon name="check" class="size-3.5" />
                        </span>
                        {{ $point }}
                    </li>
                @endforeach
            </ul>

            <div class="mt-10 flex flex-wrap items-center gap-4" data-reveal>
                <x-button :href="route('about')" variant="glow" icon="arrow-right" class="btn-shine">More about us</x-button>
                <a href="#services" class="text-sm font-semibold text-ink underline decoration-film-500 underline-offset-4 hover:decoration-glow-300">See our services</a>
            </div>
        </div>

        {{-- Image collage --}}
        <div class="relative lg:col-span-6" data-reveal>
            <div class="grid grid-cols-5 grid-rows-6 gap-4 sm:h-[34rem]">
                <div class="col-span-3 row-span-6 overflow-hidden rounded-[2rem] bg-film-700">
                    <x-xray name="angio-suite" alt="Ceiling-mounted X-ray system in a procedure room" base="xray" scan sizes="(min-width: 1024px) 380px, 60vw" class="h-full min-h-80" />
                </div>
                <div class="group col-span-2 row-span-3 overflow-hidden rounded-[2rem] bg-film-700">
                    <x-photo name="dr-wallstand" alt="Wall-mounted digital X-ray detector on a vertical stand" sizes="(min-width: 1024px) 250px, 40vw"  class="xray-hover" />
                </div>
                <div class="group col-span-2 row-span-3 overflow-hidden rounded-[2rem] bg-film-700">
                    <x-photo name="xray-mobile" alt="Mobile X-ray unit with its tube arm folded for moving" sizes="(min-width: 1024px) 250px, 40vw"  class="xray-hover" />
                </div>
            </div>
            <div class="float-slow absolute -bottom-6 left-6 flex items-center gap-3 rounded-2xl bg-film-800 p-4 pr-6 shadow-[0_24px_60px_-20px_rgb(11_45_77/0.45)] ring-1 ring-film-600 sm:left-10">
                <img src="{{ asset('images/zenray-mark-light.svg') }}" alt="" width="160" height="112" class="h-9 w-auto">
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
    <section class="bg-film-900 py-16 text-white" aria-label="ZenRay in numbers">
        <dl class="mx-auto grid max-w-7xl gap-8 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8" data-stagger>
            @foreach ($stats as $stat)
                <div class="flex flex-col-reverse border-l-2 border-signal-500 pl-6">
                    <dt class="text-sm font-medium text-navy-200">{{ $stat['label'] }}</dt>
                    <dd class="text-5xl font-bold tracking-tight">{{ $stat['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
@endif
